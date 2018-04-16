<?php

namespace Top10\CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Top10\CabinetBundle\Service\CartManager;
//use Doctrine\ORM\EntityRepository;
use Top10\CabinetBundle\Entity;
use Top10\CabinetBundle\Entity\ProductRepository;
use Top10\CabinetBundle\Entity\Cart;
use Top10\CabinetBundle\Entity\Product;

/**
 * CatalogController
 */
class CatalogController extends Controller
{
	/**
	 * @Route("/catalog", name="catalog_index")
	 * @Template()
	 */
	public function catalogAction(Request $request)
	{
		/** @var $catalogSearch \Top10\CabinetBundle\Service\CatalogSearch */
		$catalogSearch = $this->get('cabinet.catalog_search');
		$sResult = $catalogSearch->search(false, true, true);

		$cartManager = $this->get('cabinet.cart_manager');

		$result = array(
			'cntPagination' => $sResult['cntPagination'],
			'pagination' => $sResult['pagination'],
			'filterForm' => $sResult['filterForm']->createView(),
			'price_range' => $sResult['filterResult']['prices'],
			'catalogFilter' => $sResult['catalogFilter'],
			'cartinfo' => $cartManager->get(),
		);

		//return $this->redirect($this->generateUrl('homepage'), 301);
		return $this->render('Top10CabinetBundle:Catalog:catalog.html.twig', $result);
	}


	/**
	  * @Route("/catalog.xml", defaults={"_format"="xml"}, name="catalog_xml")
	  * @Template()
	*/
	public function catalogXMLAction(Request $request)
	{
		$router 	 = $this->container->get('router');
		$url		 = $router->generate('top10_cabinet_default_index', array(), true);
		$security	 = $this->get('security.context');
		$user		 = $security->getToken()->getUser();
		$em			 = $this->getDoctrine()->getManager();
		$rep		 = $em->getRepository('Top10CabinetBundle:Product');
		$catalogNode = new \SimpleXMLElement( "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><catalog></catalog>" );

		$qb = $rep->createQueryBuilder('p');
		$qb->addSelect('p')
			->andWhere('p.jsonUp = 1')
			->andWhere('p.type != :type')
				->setParameter( 'type', 'fasteners' )
			->andWhere('p.quantity > 0')
			//->andWhere('p.article = :art')
				//->setParameter( 'art', '41009890' )
			->andWhere('p.approved = :approved')
				->setParameter( 'approved', '1' )
			->addOrderBy('p.type', 'ASC');

		$products = $qb->getQuery()->getResult();

		foreach ($products as $product) {
			$itemNode = $catalogNode->addChild('product');

			$img = $url . ltrim($product->getFullPathImage(), '/');

			if ( file_exists( $_SERVER["DOCUMENT_ROOT"] . $product->getFullPathImage() ) )
				$itemNode->addChild('image', $img);

			$itemNode->addChild( 'article', $product->getArticle() );
			$itemNode->addChild( 'articleexternal', $product->getArticleExternal() );
			$itemNode->addChild( 'name', $product->getName() );

			if ( $product->getQuantity() > 50 )
				$itemNode->addChild( 'quantity', 50 );
			else
				$itemNode->addChild( 'quantity', $product->getQuantity() );

			$itemNode->addChild( 'price', $product->getPriceForUser($user) );
			if( $product->getType() == 'tire' )
				$itemNode->addChild( 'pricerecomend', $product->getPrice06() );
			$itemNode->addChild( 'type', $product->getType() );
			$itemNode->addChild( 'brand', $product->getBrand() );
			if( $product->getModel() )
				$itemNode->addChild( 'model', $product->getModel()->getName() );

			$itemPropertesNode = $itemNode->addChild( 'propertes' );
			$itemPropertesNode->addChild( 'width', $product->getWidth() );
			if( $product->getType() == 'tire' )
				$itemPropertesNode->addChild( 'height', $product->getHeight() );
			$itemPropertesNode->addChild( 'radius', $product->getRadius() );

			if( $product->getType() == 'tire' )
				$itemPropertesNode->addChild( 'season', $product->getSeason() );

			if( $product->getType() == 'disk' ){
				$itemPropertesNode->addChild( 'numberfixtures', $product->getNumberfixtures() );
				$itemPropertesNode->addChild( 'wheelbase', $product->getWheelbase() );
				$itemPropertesNode->addChild( 'et', $product->getBoom() );
				$itemPropertesNode->addChild( 'dia', $product->getCentralhole() );
				$itemPropertesNode->addChild( 'material', $product->getMaterial() );
				$itemPropertesNode->addChild( 'color', $product->getColor() );
			}
		}

        return new Response($catalogNode->asXML());
    }


	/**
	  * @Route("/catalogtest.xml", defaults={"_format"="xml"}, name="catalogtest_xml")
	  * @Template()
	*/
	public function catalogtestXMLAction(Request $request)
	{
		$router 	 = $this->container->get('router');
		$url		 = $router->generate('top10_cabinet_default_index', array(), true);
		$security	 = $this->get('security.context');
		$user		 = $security->getToken()->getUser();
		$em			 = $this->getDoctrine()->getManager();
		$rep		 = $em->getRepository('Top10CabinetBundle:Product');
		$catalogNode = new \SimpleXMLElement( "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><catalog></catalog>" );

		$qb = $rep->createQueryBuilder('p');
		$qb->addSelect('p')
			->andWhere('p.jsonUp = 1')
			->andWhere('p.type != :type')
				->setParameter( 'type', 'fasteners' )
			->andWhere('p.quantity > 0')
			->andWhere('p.article = :art')
				->setParameter( 'art', '41043603' )
			->andWhere('p.approved = :approved')
				->setParameter( 'approved', '1' )
			->addOrderBy('p.type', 'ASC');

		$products = $qb->getQuery()->getResult();

		foreach ($products as $product) {
			$itemNode = $catalogNode->addChild('product');

			$img = $url . ltrim($product->getFullPathImage(), '/');

			if ( file_exists( $_SERVER["DOCUMENT_ROOT"] . $product->getFullPathImage() ) )
				$itemNode->addChild('image', $img);

			$itemNode->addChild( 'article', $product->getArticle() );
			$itemNode->addChild( 'articleexternal', $product->getArticleExternal() );
			$itemNode->addChild( 'name', $product->getName() );

			if ( $product->getQuantity() > 50 )
				$itemNode->addChild( 'quantity', 50 );
			else
				$itemNode->addChild( 'quantity', $product->getQuantity() );

			$itemNode->addChild( 'price', $product->getPriceForUser($user) );
			if( $product->getType() == 'tire' )
				$itemNode->addChild( 'pricerecomend', $product->getPrice06() );
			$itemNode->addChild( 'type', $product->getType() );
			$itemNode->addChild( 'brand', $product->getBrand() );
			if( $product->getModel() )
				$itemNode->addChild( 'model', $product->getModel()->getName() );

			$itemPropertesNode = $itemNode->addChild( 'propertes' );
			$itemPropertesNode->addChild( 'width', $product->getWidth() );
			if( $product->getType() == 'tire' )
				$itemPropertesNode->addChild( 'height', $product->getHeight() );
			$itemPropertesNode->addChild( 'radius', $product->getRadius() );

			if( $product->getType() == 'tire' )
				$itemPropertesNode->addChild( 'season', $product->getSeason() );

			if( $product->getType() == 'disk' ){
				$itemPropertesNode->addChild( 'numberfixtures', $product->getNumberfixtures() );
				$itemPropertesNode->addChild( 'wheelbase', $product->getWheelbase() );
				$itemPropertesNode->addChild( 'et', $product->getBoom() );
				$itemPropertesNode->addChild( 'dia', $product->getCentralhole() );
				$itemPropertesNode->addChild( 'material', $product->getMaterial() );
				$itemPropertesNode->addChild( 'color', $product->getColor() );
			}
		}

        return new Response($catalogNode->asXML());
    }

	/**
	  * @Route("/catalogxml/{login}/{password}/catalog.xml", defaults={"_format"="xml"}, name="catalog_user_xml")
	  * @Template()
	*/
	public function catalogUserXMLAction( $login, $password, Request $request)
	{
		$router 	 = $this->container->get('router');
		$url		 = $router->generate('top10_cabinet_default_index', array(), true);
		$security	 = $this->get('security.context');
		$em			 = $this->getDoctrine()->getManager();
		$rep		 = $em->getRepository('Top10CabinetBundle:Product');
		$catalogNode = new \SimpleXMLElement( "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><order></order>" );
		$repUser	 = $em->getRepository('Top10CabinetBundle:User');

		$user = $repUser->findOneBy(array('emailCanonical' => $login));

		//--------------сверяем логин пароль------------------------- 
		if( $user ){
			$encoder_service = $this->get('security.encoder_factory');
			$encoder = $encoder_service->getEncoder($user);
			$encoded_pass = $encoder->encodePassword($password, $user->getSalt());

			if( $encoded_pass === $user->getPassword() ){

				$qb = $rep->createQueryBuilder('p');
				$qb->addSelect('p')
					->andWhere('p.jsonUp = 1')
					->andWhere('p.type != :type')
						->setParameter( 'type', 'fasteners' )
					->andWhere('p.quantity > 0')
					->andWhere('p.approved = :approved')
						->setParameter( 'approved', '1' )
					//->andWhere( $qb->expr()->in('p.article', array('41009890', '41009424', 'К92084')) )
					->addOrderBy('p.type', 'ASC');

				$products = $qb->getQuery()->getResult();

				foreach ($products as $product) {
					$itemNode = $catalogNode->addChild('product');

					$img = $url . 'img/' . $product->getFullPathImage();

					if ( file_exists( $_SERVER["DOCUMENT_ROOT"] . '/img/' . $product->getFullPathImage() ) )
						$itemNode->addChild('image', $img);

					$itemNode->addChild( 'article', $product->getArticle() );
					$itemNode->addChild( 'articleexternal', $product->getArticleExternal() );
					$itemNode->addChild( 'name', $product->getName() );

					if ( $product->getQuantity() > 50 )
						$itemNode->addChild( 'quantity', 50 );
					else
						$itemNode->addChild( 'quantity', $product->getQuantity() );

					$itemNode->addChild( 'price', $product->getPriceForUser( $user ) );
					if( $product->getType() == 'tire' )
						$itemNode->addChild( 'pricerecomend', $product->getPrice06() );
					$itemNode->addChild( 'type', $product->getType() );
					$itemNode->addChild( 'brand', $product->getBrand() );
					if( $product->getModel() )
						$itemNode->addChild( 'model', $product->getModel()->getName() );

					$itemPropertesNode = $itemNode->addChild( 'propertes' );
					$itemPropertesNode->addChild( 'width', $product->getWidth() );
					if( $product->getType() == 'tire' )
						$itemPropertesNode->addChild( 'height', $product->getHeight() );
					$itemPropertesNode->addChild( 'radius', $product->getRadius() );

					if( $product->getType() == 'tire' )
						$itemPropertesNode->addChild( 'season', $product->getSeason() );

					if( $product->getType() == 'disk' ){
						$itemPropertesNode->addChild( 'numberfixtures', $product->getNumberfixtures() );
						$itemPropertesNode->addChild( 'wheelbase', $product->getWheelbase() );
						$itemPropertesNode->addChild( 'et', $product->getBoom() );
						$itemPropertesNode->addChild( 'dia', $product->getCentralhole() );
						$itemPropertesNode->addChild( 'material', $product->getMaterial() );
						$itemPropertesNode->addChild( 'color', $product->getColor() );
					}
				}

			}
			else
				$itemNode = $catalogNode->addChild('error', 'not a valid login or password');
		}
		else 
			$itemNode = $catalogNode->addChild('error', 'not a valid login or password');

		return new Response($catalogNode->asXML());
	}

    /**
     * @Route("/catalog/filter", name="catalog_filter")
     * @Method("POST|GET")
     * @Template()
     */
    public function fitlerAjaxAction(Request $request)
    {
        /** @var $catalogSearch \Top10\CabinetBundle\Service\CatalogSearch */
        $catalogSearch = $this->get('cabinet.catalog_search');
        $sResult = $catalogSearch->search(false, false, true);
        $filterResult = $sResult['filterResult'];

        $json = array(
            'result' => 'success',
        );
        $json['filters'] = $filterResult['filters'];
        $json['price_range'] = $filterResult['prices'];

        $response = new Response(json_encode($json));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;

    }

    /**
     * @Route("/catalog/article", name="catalog_article")
     * @Method("GET|POST")
     * @Template()
     */
    public function searchArticleAction(Request $request)
    {
        /** @var $productRep ProductRepository */
        $productRep = $this->getDoctrine()->getRepository('Top10CabinetBundle:Product');
        $article = $request->get('article', null);
        //$products = $productRep->findBy(array('article' => $article));

		$qb = $productRep->createQueryBuilder('p');

		$qb->addSelect('p')
			->andWhere('p.article = :article')
				->setParameter( 'article', $article )
			->andWhere('p.approved = :approved')
				->setParameter( 'approved', '1' );

		$products = $qb->getQuery()->getResult();

        /** @var $catalogSearch \Top10\CabinetBundle\Service\CatalogSearch */
        $catalogSearch = $this->get('cabinet.catalog_search');
        $sResult = $catalogSearch->search(false, true, true);

        /** @var $cartManager CartManager */
        $cartManager = $this->get('cabinet.cart_manager');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $products,
            $this->get('request')->query->get('page', 1) /*page number*/,
            20/*limit per page*/
        );

		

        $result = array(
            'pagination' => $pagination,
            'filterForm' => $sResult['filterForm']->createView(),
            'price_range' => $sResult['filterResult']['prices'],
            'catalogFilter' => $sResult['catalogFilter'],
            'cartinfo' => $cartManager->getUserCartInfo(),
        );

        return $this->render('Top10CabinetBundle:Catalog:catalog.html.twig', $result);

    }


    /**
     * @Route("/cart/add", name="catalog_add_to_cart")
     * @Method("POST")
     * @Template()
     */
    public function addToCartAction(Request $request)
    {
        $id = $request->request->get('product');
        $count = $request->request->get('count' . $id);

        /** @var $cartManager CartManager */
        $cartManager = $this->get('cabinet.cart_manager');
        $result = $cartManager->addToCard($id, $count);
        if(null !== $result ) {
            $msg = '';
            foreach($result['added'] as $p) {
                /** @var $p Entity\Product */
                $msg .= sprintf('"%s" добавлен в корзину', $p->getName()) . PHP_EOL;
            }
        }
        else {
            $msg = "Ошибка при добавлении товара в корзину. Обратитесь, пожалуйста, в техподдержку.";
        }
        $this->get('session')->getFlashBag()->add('alert-success', $msg);
        return $this->redirect(
            $this->generateUrl(
                'catalog_index',
                array(
                    "f"=> $request->query->get("f")
                )
            )
        );
    }
}
