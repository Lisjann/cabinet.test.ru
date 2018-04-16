<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

use Top10\CabinetBundle\Form\Model\CatalogFilter;
use Top10\CabinetBundle\Entity;

class ProductRepository extends EntityRepository
{
	/**
	 * Осуществляет поиск товаров по параметрам и сохраняет результат во временную таблицу
	 * Далее осуществляется группировка полей для фильтра
	 *
	 * В итоге возвращается массив с товарами и значениями для сгруппированных полей
	 *
	 * @param \Top10\CabinetBundle\Form\Model\CatalogFilter $catalogFilter фильтры
	 * @param boolean $excludeProducts считать только фильтры или возвращать товары
	 * @param bool $countAbsPrices максимальная минимальная цена без учета фильтров
	 * @param bool $countCPrices разброс цен
	 * @throws \RuntimeException
	 * @return array
	 */
	public function getProductsEx(CatalogFilter &$catalogFilter, $excludeProducts = false, $countAbsPrices = true, $countCPrices = true, $catalogSort = array())
	{
		$fResult = array(
			'products' => array(),
			'filters' => array(),
			'prices' => array(
				'cmin' => 0,
				'cmax' => 0,
				'absmin' => 0,
				'absmax' => 0,
			),
		);

		$em = $this->getEntityManager();

		/** @var $dbConn Connection */
		$dbConn = $em->getConnection();
		$queryParams = array();


		$whereAll = array();

		$whereAll['approved'] = array(
			'where' => '(product.approved = 1 )'
		);

		// количество больше нуля
		$whereAll['quantity'] = array(
			'where' => '(product.quantity > 0 OR product.quantityres > 0)'
		);

		$catalogType = $catalogFilter->getType();

		$priceType14 = $catalogFilter->getPriceType('disk', 'price01');
		$priceType41 = $catalogFilter->getPriceType('tire', 'price01');
		$priceType = $catalogType ? $catalogFilter->getPriceType($catalogType, 'price01') : ($priceType14 === $priceType41 ? $priceType14 : null);

		// цена больше нуля
		if( $priceType ) {
			$whereAll['price'] = array(
				'where' => sprintf('product.%s >= 0', $priceType)
			);
		}
		else {
			$whereAll['price'] = array(
				'where' => sprintf(
					'((product.type = "disk" AND product.%1$s >= 0) OR (product.type = "tire" AND product.%2$s >= 0))',
					$priceType14, $priceType41
			));
		}

		$allParams = $catalogFilter->getSharedParamList();
		if($catalogType) {
			$allParams = array_merge($allParams, $catalogFilter->getParamList($catalogType));

			// цена больше нуля
			$whereAll['type'] = array(
				'where' => 'product.type = :type',
			);
			$queryParams['type'] = $catalogType;
		}
		// составляем WHERE
		foreach($allParams as $paramName) {
			$paramValue = $catalogFilter->{$paramName};

			if( $paramValue !== null ) {
				$paramValue = str_replace(",", ".", $paramValue);

				if( in_array($paramName, array( "boom", "wheelbase", "centralhole", "maxspeed", "width", "height", "radius", "maxload" )) ) {
					//$paramValue = number_format( (float) $paramValue, 2, ".", "" );
					$paramValue = (float) $paramValue;
				}

				if( $paramName == 'price_from' ) {
					if($priceType) {
						$whereAll[$paramName] = array(
							'where' => sprintf('product.%1$s >= :%2$s', $priceType, $paramName),
						);
					}
					else {
						$whereAll[$paramName] = array(
							'where' => sprintf(
								'((product.type = "disk" AND product.%1$s >= :%3$s) OR (product.type = "tire" AND product.%2$s >= :%3$s))',
								$priceType14, $priceType41, $paramName
						));
					}

					$queryParams[$paramName] = $paramValue;
				}
				elseif( $paramName == 'price_to' ) {
					if($priceType) {
						$whereAll[$paramName] = array(
							'where' => sprintf('product.%1$s <= :%2$s', $priceType, $paramName),
						);
					}
					else {
						$whereAll[$paramName] = array(
							'where' => sprintf(
								'((product.type = "disk" AND product.%1$s <= :%3$s) OR (product.type = "tire" AND product.%2$s <= :%3$s))',
								$priceType14, $priceType41, $paramName
						));
					}

					$queryParams[$paramName] = $paramValue;
				}
				elseif( $paramName == 'factory' ) {
					$whereAll[$paramName] = array(
						'where' => sprintf('product.%1$s_id = :%1$s', $paramName),
						//'where' => sprintf('product.%1$s_id = %1$s.id', $paramName),
					);
					$queryParams[$paramName] = $paramValue;
				}
				elseif( $paramName == 'pcd' ) {
					// concat with separator
					$whereAll[$paramName] = array(
						'where' => sprintf('CONCAT_WS("*",product.numberfixtures,product.wheelbase) = :%1$s', $paramName),
					);
					$queryParams[$paramName] = $paramValue;
				}
				else {
					$whereAll[$paramName] = array(
						'where' => sprintf('product.%1$s = :%1$s', $paramName),
					);
					$queryParams[$paramName] = $paramValue;


				}
			}
		}

		$whereAll[] = array(
			'where' => sprintf('product.type != "fasteners"'),
		);

		// делаем временную таблицу для группировки пустых аттрибутов
		$productQB = $em->createQueryBuilder();
		foreach($whereAll as $whereName => $r) {
			$productQB->andWhere($r['where']);
		}

		$productQB->andWhere('product.factory_id = factory.id');//соединяем Продукты со Складами

		$productWhereSQL = self::getWhereSQL($productQB);

		//вставка сортировки в запрос
		$productQB = $em->createQueryBuilder();

		if( $catalogSort ){
			if( $catalogSort['sort'] == 'price' && $priceType )
				$productQB->addOrderBy( $priceType, $catalogSort['direction']);
			else
				$productQB->addOrderBy( $catalogSort['sort'], $catalogSort['direction'] );
		}
		else
			$productQB->addOrderBy('product.name', 'ASC');
		
		$productOrderSQL = self::getOrderSQL($productQB);

		$sql = '
			CREATE TEMPORARY TABLE product_temp AS (
				SELECT product.*
					  ,factory.name factory
					  ,(SELECT SUM(po.quantityaccept)
						FROM ProductsOrders po
						WHERE po.product_id = product.id
						AND po.quantityaccept > 0
						AND po.created > (NOW() - INTERVAL 12 MONTH)
						) quantityinorders
				FROM Product as product
					,Factory as factory
				'. $productWhereSQL . $productOrderSQL .'
			);
		';

		$stmt = $dbConn->prepare($sql);
		foreach($queryParams as $param => $value) {
			$stmt->bindValue($param, $value);
		}
		$creationSuccess = $stmt->execute();
		if( $creationSuccess === false ) {
			throw new \RuntimeException('Can`t create TEMPORARY table');
		}

		######
		### товары
		if( !$excludeProducts ) {
			$rsm = new ResultSetMappingBuilder($em);
			$rsm->addRootEntityFromClassMetadata('Top10\CabinetBundle\Entity\Product', 'product');

			$query = $em->createNativeQuery('', $rsm);

			$query->setSQL('
				SELECT product.*
					FROM product_temp as product
			');

			/** @var $products Entity\Product[] */
			$products = $query->getResult();
			#$method = "get" . ucfirst($priceType);
			#foreach( $products as &$item ) {
			#    $item->setPrice($item->{$method}());
			#}

			$fResult['products'] = $products;
		}


		######
		### группировка для пустых аттрибутов(простая)
		$paramsToGroup = array();
		$exclude = array( 'price_from','price_to','name' );
		$decAttrs = array( "boom", "wheelbase", "centralhole", "maxspeed", "width", "height", "radius", "maxload" );
		foreach( $allParams as $paramName ) {
			if( in_array($paramName, $exclude) ) {
				continue;
			}

			$paramValue = $catalogFilter->{$paramName};
			if( $paramValue !== null ) {
				$paramsToGroup[] = $paramName;
				continue;
			}

			if( in_array($paramName, $decAttrs) ) {
				$sql = '
					SELECT CAST(product.%1$s as DECIMAL) as decval, product.%1$s
					FROM product_temp as product
					GROUP BY %1$s
					ORDER BY decval ASC;
				';
			}
			elseif( $paramName == 'pcd' ) {
				$sql = '
					SELECT CONCAT_WS("*",product.numberfixtures,product.wheelbase) as pcd,
						product.numberfixtures, CAST(product.wheelbase as DECIMAL) as wb_dec
					FROM product_temp as product
					GROUP BY pcd
					ORDER BY product.numberfixtures ASC, wb_dec ASC;
				';
			}
			elseif( $paramName == 'factory' ) {
				$sql = '
					SELECT product.%1$s_id as decval, product.%1$s
					FROM product_temp as product
					WHERE product.type != "fasteners"
					GROUP BY product.%1$s
					ORDER BY product.%1$s ASC;
				';
			}
			else {
				$sql = '
					SELECT product.%1$s
					FROM product_temp as product
					GROUP BY product.%1$s
					ORDER BY product.%1$s ASC;
				';
			}
			$sql = sprintf($sql, $paramName);

			$stmt = $dbConn->prepare($sql);
			foreach($queryParams as $param => $value) {
				$stmt->bindValue($param, $value);
			}
			$stmt->execute();
			$dbResult = $stmt->fetchAll();

			$result = array();
			foreach($dbResult as $row) {
				if(!empty($row[$paramName])) {
/*print'<pre>';
print_r( $row );
print'</pre>';*/
					if( $paramName == 'factory' && $row['decval'] )
						$result[$row['decval']] = $row[$paramName];
					else
						$result[$row[$paramName]] = $row[$paramName];
				}
            }

            $fResult['filters'][$paramName] = $result;
        }

		######
		### группировка для непустых аттрибутов(сложная)
		foreach($paramsToGroup as $paramName) {
			// делаем where для аттрибута без его участия
			$qb = $em->createQueryBuilder();
			foreach($whereAll as $whereName => $r) {
				if( $whereName == $paramName ) {
					continue;
				}
				$qb->andWhere($r['where']);
			}

			$qb->andWhere('product.factory_id = factory.id');//соединяем Продукты со Складами

			$attrWhereSQL = self::getWhereSQL($qb);

			if( in_array($paramName, $decAttrs) ) {
				$sql = '
					SELECT CAST(product.%1$s as DECIMAL) as decval
						  ,product.%1$s
						FROM Product as product
							,Factory as factory
						'.$attrWhereSQL.'
						GROUP BY %1$s
						ORDER BY decval ASC;
				';
			}
			elseif( $paramName == 'pcd' ) {
				$sql = '
					SELECT CONCAT_WS("*",product.numberfixtures,product.wheelbase) as pcd
						  ,product.numberfixtures
						  ,CAST(product.wheelbase as DECIMAL) as wb_dec
					FROM Product as product
						,Factory as factory
					'.$attrWhereSQL.'
					GROUP BY pcd
					ORDER BY product.numberfixtures ASC
						 ,wb_dec ASC;
				';
			}
			elseif( $paramName == 'factory' ) {
				$sql = '
				SELECT product.%1$s_id as decval, %1$s.name as %1$s
				FROM Product as product
					,Factory as factory
				'.$attrWhereSQL.'
				GROUP BY product.%1$s_id
				ORDER BY product.%1$s_id ASC;
				';

					$whereAll[$paramName] = array(
						'where' => sprintf('product.%1$s_id = :%1$s', $paramName),
					);
					$queryParams[$paramName] = $paramValue;
			}
			else {
				$sql = '
					SELECT product.%1$s
					FROM Product as product
						,Factory as factory
					'.$attrWhereSQL.'
					GROUP BY product.%1$s
					ORDER BY product.%1$s ASC;
				';
			}

			$sql = sprintf($sql, $paramName);
			$stmt = $dbConn->prepare($sql);
			foreach($queryParams as $qParam => $value) {
				if( $qParam == $paramName ) {
					continue;
				}
				$stmt->bindValue($qParam, $value);
			}
			$stmt->execute();
			$dbResult = $stmt->fetchAll();

			$result = array();
			foreach($dbResult as $row) {
				if(!empty($row[$paramName])) {
					if( $paramName == 'factory' && $row['decval'] )
						$result[$row['decval']] = $row[$paramName];
					else
						$result[$row[$paramName]] = $row[$paramName];
				}
			}

			$fResult['filters'][$paramName] = $result;
		}

		$catalogFilter->setFilters($fResult['filters']);

		######
		### минимальная, максимальная цена
		if( $countAbsPrices ) {
			if($priceType) {
				$sql = '
					SELECT MIN(product.%1$s) as min
						  ,MAX(product.%1$s) as max
					FROM Product as product
					LIMIT 1
				';
				$sql = sprintf($sql, $priceType);
				$stmt = $dbConn->prepare($sql);
				$stmt->execute();
				$dbResult = $stmt->fetch(\PDO::FETCH_ASSOC);
				$min = $dbResult['min'];
				$max = $dbResult['max'];
			}
			else {
				$sql = '
					SELECT MIN(product.%1$s) as min1
						  ,MAX(product.%1$s) as max1
						  ,MIN(product.%2$s) as min2
						  ,MAX(product.%2$s) as max2
					FROM Product as product
					LIMIT 1
				';
				$sql = sprintf($sql, $priceType14, $priceType41);
				$stmt = $dbConn->prepare($sql);
				$stmt->execute();
				$dbResult = $stmt->fetch(\PDO::FETCH_ASSOC);
				$min = min($dbResult['min1'], $dbResult['min2']);
				$max = max($dbResult['max1'], $dbResult['max2']);
			}

			$fResult['prices']['absmin'] = $min;
			$fResult['prices']['absmax'] = $max;
			if( !$catalogFilter->price_from ) {
				$catalogFilter->price_from = $min;
			}
			if( !$catalogFilter->price_to ) {
				$catalogFilter->price_to = $max;
			}
		}

		######
		### минимальная, максимальная цена
		if( $countCPrices ) {
			// делаем where для цены без её участия
			$qb = $em->createQueryBuilder();
			foreach($whereAll as $whereName => $r) {
				if( $whereName == 'price_from' || $whereName == 'price_to' ) {
					continue;
				}
				$qb->andWhere($r['where']);
			}
			$priceWhereSQL = self::getWhereSQL($qb);

			if($priceType) {
				$sql = '
					SELECT MIN(product.%1$s) as min
						  ,MAX(product.%1$s) as max
					FROM Product as product
						,Factory as factory
					'.$priceWhereSQL.'
					LIMIT 1
				';

				$sql = sprintf($sql, $priceType);
				$stmt = $dbConn->prepare($sql);
				foreach($queryParams as $qParam => $value) {
					if( $qParam == 'price_from' || $qParam == 'price_to' ) {
						continue;
					}
					$stmt->bindValue($qParam, $value);
				}
				$stmt->execute();
				$dbResult = $stmt->fetch(\PDO::FETCH_ASSOC);

				$fResult['prices']['cmin'] = $dbResult['min'];
				$fResult['prices']['cmax'] = $dbResult['max'];
			}
			else {
				$sql = '
					SELECT MIN(product.%1$s) as min1
						  ,MAX(product.%1$s) as max1
						  ,MIN(product.%2$s) as min2
						  ,MAX(product.%2$s) as max2
					FROM Product as product
						,Factory as factory
					'.$priceWhereSQL.'
					LIMIT 1
				';

				$sql = sprintf($sql, $priceType14, $priceType41);
				$stmt = $dbConn->prepare($sql);
				foreach($queryParams as $qParam => $value) {
					if( $qParam == 'price_from' || $qParam == 'price_to' ) {
						continue;
					}
					$stmt->bindValue($qParam, $value);
				}
				$stmt->execute();
				$dbResult = $stmt->fetch(\PDO::FETCH_ASSOC);
				$min = min($dbResult['min1'], $dbResult['min2']);
				$max = max($dbResult['max1'], $dbResult['max2']);
				$fResult['prices']['cmin'] = $min;
				$fResult['prices']['cmax'] = $max;
			}

		}

		return $fResult;
	}

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @return string SQL WHERE part
     */
    public static function getWhereSQL(\Doctrine\ORM\QueryBuilder $qb)
    {
        $wherePart = $qb->getDQLPart('where');
        $whereSQL = (string) $wherePart;
        if(!empty($whereSQL)) {
            $whereSQL = ' WHERE '.$whereSQL;
        }
        return $whereSQL;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @return string SQL ORDER BY part
     */
    public static function getOrderSQL(\Doctrine\ORM\QueryBuilder $qb)
    {
        /** @var $orderPart \Doctrine\ORM\Query\Expr\OrderBy */
        $orderPart = $qb->getDQLPart('orderBy');
        $orderSQL = implode(', ', (array) $orderPart);
        if(!empty($orderSQL)) {
            $orderSQL = ' ORDER BY '.$orderSQL;
        }
        return $orderSQL;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @return string SQL ORDER BY part
     */
    public static function getGroupSQL(\Doctrine\ORM\QueryBuilder $qb)
    {
        /** @var $orderPart \Doctrine\ORM\Query\Expr\OrderBy */
        $orderPart = $qb->getDQLPart('orderBy');
        $orderSQL = implode(', ', (array) $orderPart);
        if(!empty($orderSQL)) {
            $orderSQL = ' ORDER BY '.$orderSQL;
        }
        return $orderSQL;
    }

	/**
	 * Сбрасывает флаг успешного обновления у всех товаров.
	 * Используется в начале обновления
	 * @param string $type
	 */
	public function setJsonUpdates($type, $factory, $jsonup = 0)
	{
		if ( $type == null && $factory == null )
			return false;

		/** @var EntityManager $em */
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();
		$qb->update('Top10CabinetBundle:Product','p')
			->set('p.jsonUp', $jsonup);
			
		if( $type != null )
			$qb->andWhere($qb->expr()->like('p.type', ':type'))
				->setParameter('type',$type);

		if( $factory != null )
			$qb->andWhere('p.factory = :factory')
				->setParameter('factory', $factory);
			
		$q = $qb->getQuery();
		$q->execute();

		return true;
	}

    /**
     * Возвращает список товаров, в зависимости от поля JsonUpdate и Type
     * @param string $type
     * @return array
     */
    public function getJsonUpdates($type, $jsonup = 0)
    {
        if ($type == "") {
            return false;
        }
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $q = $qb->select('p')
            ->from('Top10CabinetBundle:Product','p')
            ->where($qb->expr()->like('p.type', ':type'))
            ->andWhere('p.jsonUp = :jsonUp')
            ->setParameter('type',$type)
            ->setParameter('jsonUp',$jsonup)
            ->getQuery();

        $results = $q->getResult();

        return $results;
    }

    /**
     * Обновляет (обнуляет) цены и количество товаров, которые не попали в 6,json
     * @param mixed $articles
     */
    public function updateProductPrices($type, $factory, $quantityres = false)
    {
        if ( $type == null && $factory == null )
			return false;

        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->update('Top10CabinetBundle:Product','p')
           ->where('p.jsonUp = :jsonUp')
           ->setParameter('jsonUp',0)
           ->set('p.price01', 0)
           ->set('p.price02', 0)
           ->set('p.price03', 0)
           ->set('p.price04', 0)
           ->set('p.price05', 0)
           ->set('p.quantity', 0);

		if( $type != null )
			$qb->andWhere($qb->expr()->like('p.type', ':type'))
				->setParameter('type',$type);

		if( $factory != null )
			$qb->andWhere('p.factory = :factory')
				->setParameter('factory', $factory);

		if( $quantityres == true )
			$qb->set('p.quantityres', 0);

		$q = $qb->getQuery();
        $q->execute();

        return true;
    }

	public function updateProductPricesAndQuantityres($type)
    {
        if ($type == "") {
            return false;
        }
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $q = $qb->update('Top10CabinetBundle:Product','p')
            ->where($qb->expr()->like('p.type', ':type'))
            ->andWhere('p.jsonUp = :jsonUp')
            ->setParameter('type',$type)
            ->setParameter('jsonUp',0)
            ->set('p.price01', 0)
            ->set('p.price02', 0)
            ->set('p.price03', 0)
            ->set('p.price04', 0)
            ->set('p.price05', 0)
            ->set('p.quantity', 0)
            ->set('p.quantityres', 0)
            ->getQuery();
        $q->execute();

        return true;
    }
}