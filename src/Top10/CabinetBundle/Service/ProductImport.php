<?php

namespace Top10\CabinetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bridge\Monolog\Logger;

use Top10\CabinetBundle\Entity\Product;
use Doctrine\ORM\EntityRepository;

class ProductImport
{
	protected $container;
	protected $logger;
	protected $em;
	protected $settings;

	public function __construct(ContainerInterface $container, EntityManager $em, array $settings, Logger $logger)
	{
		$this->logger = $logger;
		$this->container = $container;
		$this->em = $em;
		$this->settings = $settings;
	}

    /**
     * Обновление товаров из базы купиколес
     *
     * @param OutputInterface $output
     * @return bool|null
     */
    public function import(OutputInterface $output)
    {
        $time = -microtime(true);

        $products = array();
        /** @var $productRepo EntityRepository */
        $productRepo = $this->em->getRepository('Top10CabinetBundle:Product');

        $link = mysql_connect($this->settings['database_host'], $this->settings['database_user'], $this->settings['database_password']);
        $isSelect = mysql_select_db($this->settings['database_name'], $link);
        mysql_query("SET NAMES UTF8");

        if($isSelect === true ) {
            $output->writeln('Success conected to qpkolesa db');
        }
        else {
            $output->writeln('Failed to connect to qpkolesa db');
            return null;
        }

        $sql = 'SELECT
				  types.id as type_id,
				  brands.id as brand_id,
				  brands.name as brand_name,
				  models.id as model_id,
				  models.name as model_name,
				  products.*,
				  tsena,
				  price.ostatok ostatok
				FROM `rimeks_products` as types
					LEFT JOIN `rimeks_products` as brands ON brands.top = types.id
					LEFT JOIN `rimeks_products` as models ON models.top = brands.id
					INNER JOIN `rimeks_products` as products ON products.top = models.id
					LEFT JOIN `rimeks_prices` as price ON products.fcode = price.code AND products.fclasscode = price.classcode
				WHERE types.top = 0';

        $res = mysql_query($sql);
        $i = 1;
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $images = false;
            $sql = "SELECT child_serialized_array, id FROM rimeks_prodimages WHERE top = '" . $row["top"] . "'";
            $result = mysql_query($sql);
            while ($img = mysql_fetch_array($result, MYSQL_ASSOC)) {

                $ids = unserialize($img["child_serialized_array"]);
                if (is_array($ids) && !in_array($row["id"], $ids)) continue;

                if (file_exists("web/img/prodimages" . $img["id"] . "0.jpg")) {
                    $images = "prodimages" . $img["id"] . "0.jpg";
                    break;
                }
                if (file_exists("web/img/prodimages" . $img["id"] . "0.jpeg")) {
                    $images = "prodimages" . $img["id"] . "0.jpg";
                    break;
                }
                if (file_exists("web/img/prodimages" . $img["id"] . "0.png")) {
                    $images = "prodimages" . $img["id"] . "0.png";
                    break;
                }
            }
            if (!$images && file_exists("web/img/products" . $row["top"] . "0.jpg"))
                $images = "products" . $row["top"] . "0.jpg";
            if (!$images && file_exists("web/img/products" . $row["top"] . "0.jpeg"))
                $images = "products" . $row["top"] . "0.jpg";
            if (!$images && file_exists("web/img/products" . $row["top"] . "0.png"))
                $images = "products" . $row["top"] . "0.png";

            $article = ltrim($row['fcode'], "0");

            if ($article) {
                $product = $productRepo->findOneBy(array('article' => $article));
                if(!$product) {
                    $product = new Product();
                    $product->setArticle($article);
                    $this->em->persist($product);
                }

                $brand = trim($row["brand_name"]);
                if($brand == 'K&amp;K') {
                    $brand = 'K&K';
                }
                $product->setName(trim($row["name"]));
                $product->setType(trim($row['type']));
                $product->setRadius( (trim($row["f" . $row["type"] . "_diametr"]) == '0.00' ? "" : trim($row["f" . $row["type"] . "_diametr"])) );
                $product->setWidth( (trim($row["f" . $row["type"] . "_shirina"]) == '0.00' ? "" : trim($row["f" . $row["type"] . "_shirina"])) );
                $product->setNumberfixtures( (trim($row["fdisk_kolvokrep"]) == '0' ? "" : trim($row["fdisk_kolvokrep"])) );
                $product->setWheelbase( (trim($row["fdisk_mejosevoerast"]) == '0.00' ? "" : trim($row["fdisk_mejosevoerast"])) );
                $product->setBoom( (trim($row["fdisk_vilet"]) == '0.00' ? "" : trim($row["fdisk_vilet"])) );
                $product->setCentralhole( (trim($row["fdisk_centralnoeotv"]) == '0.00' ? "" : trim($row["fdisk_centralnoeotv"])) );
                $product->setMaterial( trim($row["fdisk_material"]) );
                $product->setSeason( trim($row["ftire_sezonnost"]) );
                $product->setHeight( (trim($row["ftire_visota"]) == '0.00' ? "" : trim($row["ftire_visota"])) );
                $product->setMaxload( (trim($row["ftire_maxnagr"]) == '0.00' ? "" : trim($row["ftire_maxnagr"])) );
                $product->setMaxspeed( (trim($row["ftire_maxspeed"]) == '0.00' ? "" : trim($row["ftire_maxspeed"])) );
                $product->setCamera(trim($row["ftire_iscamera"]));
                $product->setImage($images);
                $product->setBrand($brand);
				if ( $product->getPrice03() == 0 ){ $product->setPrice03( $row['tsena'] ); }
				if ( $product->getQuantity() == 0 ){ $product->setQuantity( $row['ostatok'] ); }
            }

            if ($i++ % 100 === 0) {
                $this->em->flush();
                $this->em->clear();
                $time += microtime(true);
                $output->writeln(sprintf('Обработано %d за %f', $i, $time / 1000000000));
            }
        }

        $output->writeln('Конец загрузки из базы. Сохраняем '. $i);
        $this->em->flush();
        mysql_close($link);

        return true;

    }

}