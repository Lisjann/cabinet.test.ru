<?php

namespace Top10\CabinetBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;

use Top10\CabinetBundle\Entity;
use Top10\CabinetBundle\Entity\Product;
use Top10\CabinetBundle\Entity\User;
use Top10\CabinetBundle\Entity\Cart;
use Top10\CabinetBundle\Entity\Cabinetorder;
use Top10\CabinetBundle\Entity\ProductsOrders;
use Top10\CabinetBundle\Entity\Supply;
use Top10\CabinetBundle\Entity\DeliveryType;
use Top10\CabinetBundle\Entity\File;
use Top10\CabinetBundle\Entity\ProductRepository;

use Top10\CabinetBundle\Form\OrderFilterForm;
use Top10\CabinetBundle\Form\SupplyForm;
use Top10\CabinetBundle\Form\Model\CatalogFilter;
use Top10\CabinetBundle\Form\CatalogFilterForm;

use Top10\CabinetBundle\Service\CartManager;
use Top10\CabinetBundle\Service\JsonImport;

use Symfony\Component\HttpFoundation\Response;



class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
	public function indexAction()
    {
        /** @var $catalogSearch \Top10\CabinetBundle\Service\CatalogSearch */
        $catalogSearch = $this->get('cabinet.catalog_search');
        $sResult = $catalogSearch->search(false, true, true);

        $cartManager = $this->get('cabinet.cart_manager');

        $result = array(
            'filterForm' => $sResult['filterForm']->createView(),
            'price_range' => $sResult['filterResult']['prices'],
            'catalogFilter' => $sResult['catalogFilter'],
            'cartinfo' => $cartManager->get(),
        );

        return $result;

    }

	/**
     * prevention.
     *
     * @Route("/prevention/", name="prevention")
     * @Template("Top10CabinetBundle:Default:prevention.html.twig")
     */
	public function preventionAction(){
		return array();
	}

	/**
     * reviews.
     *
     * @Route("/reviews", name="reviews")
     * @Template("Top10CabinetBundle:Default:reviews.html.twig")
     */
	public function reviewsAction(){
		/** @var $cartObj CartManager */
        $cartObj = $this->get('cabinet.cart_manager');
        $cart = $cartObj->getUserCartInfo();
        $result['cartinfo'] = $cart;

        return $result;
	}

    /**
     * @Route("/cart", name="cart")
     * @Template()
     */
    public function cartAction()
	{
    	$security 		= $this->get('security.context');
    	$current_user 	= $security->getToken()->getUser();
    	$cart 			= $current_user->getCarts();
    	if (!$cart) throw $this->createNotFoundException('Unable to find cart entitys by user.');
    	
    	$count_disk = 0;
    	$count_tire = 0;
    	$cart_disk = array();
    	$cart_tire = array();
    	foreach ($cart as $item){

			if($item->getProduct()->getType() == "disk"){
				$cart_disk[$item->getProduct()->getFactory()->getSapid()][] = $item;
    			$count_disk += $item->getPrice()*$item->getQuantity();
    		}
    		if($item->getProduct()->getType() == "tire"){
    			$cart_tire[$item->getProduct()->getFactory()->getSapid()][] = $item;
    			$count_tire += $item->getPrice()*$item->getQuantity();
    		}
    	}

		//-----спиcок заказов Дисков для добавления из корзины------------
		$paginator = $this->get('knp_paginator');

		/** @var $orderRepo EntityRepository */
		$orderRepo = $this->getDoctrine()->getRepository('Top10CabinetBundle:Cabinetorder');


		$qb = $orderRepo->createQueryBuilder('o');
		$qb
			->addSelect('o')
			->andWhere('o.status < 4')
			->leftJoin( 'o.supply', 's')
			->andWhere('s.statussupply NOT IN (:statussupply)')
				->setParameter('statussupply', array( 1, 13, 20 ))
			->andWhere( 'o.created > :threeday' )
				->setParameter( 'threeday',  date( 'Y.m.d H:i:s', mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")) ) )
			->andWhere('o.user = :user')
				->setParameter('user', $current_user)
			->andWhere('o.todelete = 0')
			->andWhere('o.type = :type')
				->setParameter('type', 'disk')
			->addOrderBy('o.created', 'DESC')
			->setMaxResults( 10 );

		if( $current_user->getId() == 100000 ){
			//$qb->andWhere('o.statussupply IS NULL');
		}

		$paginator->setDefaultPaginatorOptions(array('pageParameterName'=> 'pagedisk'));

		$diskPagination = $paginator->paginate( $qb, $this->get('request')->query->get('pagedisk', 1), 10 );


		//-----спиcок заказов Шин для добавления из корзины------------
		/** @var $orderRepo EntityRepository */
		$orderRepo = $this->getDoctrine()->getRepository('Top10CabinetBundle:Cabinetorder');

		$qb = $orderRepo->createQueryBuilder('o');
		$qb
			->addSelect('o')
			->andWhere('o.status < 4')
			->leftJoin( 'o.supply', 's')
			->andWhere('s.statussupply NOT IN (:statussupply) OR s.statussupply IS NULL')
				->setParameter('statussupply', array( 1, 13, 20 ))
			->andWhere( 'o.created > :threeday' )
				->setParameter( 'threeday',  date( 'Y.m.d H:i:s', mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")) ) )
			->andWhere('o.user = :user')
				->setParameter('user', $current_user)
			->andWhere('o.todelete = 0')
			->andWhere('o.type = :type')
				->setParameter('type', 'tire')
			->addOrderBy('o.created', 'DESC')
			->setMaxResults( 10 );

		if( $current_user->getId() == 100000 ) {

			//$qb->andWhere('o.statussupply IS NULL');
		}

		$paginator->setDefaultPaginatorOptions(array('pageParameterName'=> 'pagetire'));

		$tirePagination = $paginator->paginate( $qb, $this->get('request')->query->get('pagetire', 1), 10 );


    	$result = array(
    		'cartDisk' 	=> $cart_disk,
    		'cartTire' 	=> $cart_tire,
    		"countDisk" => $count_disk,
    		"countTire" => $count_tire,
			'tirePagination' => $tirePagination,
			'diskPagination' => $diskPagination,
    	);

        $cartObj = $this->get('cabinet.cart_manager');
    	$cart = $cartObj->get();
    	$result = array_merge($result, array(
            "cartinfo" => $cart
    	));


		if( $current_user->getId() == 100000 )
			$this->get('session')->getFlashBag()->add('alert alert-success alert-dismissable alert-top', "<p><strong>Внимание!</strong> Уважаемые клиненты, мы изменили схему резервирования позиций</p><p>Для того чтобы поставить товар в резерв вам необходимо <strong>добавить позицию</strong> в уже ранее <strong>созданный заказ</strong></p><p><strong>Новый заказ</strong> можно создать в случае:</p><ul><li>Если ранее созданные заказы старше <strong>3 дней</strong>;</li><li>Если у ранее созданных заказов статус <strong>Отменен</strong>;</li><li>Если по ранее созданным заказам была отправленна <strong>Поставка</strong>;</li></ul>");

		return $this->render('Top10CabinetBundle:Default:cart.html.twig', $result);

    }
    
    /**
     * Deletes a cart entity.
     *
     * @Route("/cart/{id}/cartdelete", name="cart_delete")
     */
    public function cartdeleteAction($id)
    {
    	$cart = $this->getDoctrine()->getRepository('Top10CabinetBundle:Cart')->find($id);
    	if (!$cart) {
    		throw $this->createNotFoundException('Unable to find cart entity.');
    	}
    	$em = $this->getDoctrine()->getEntityManager();
    	$em->remove($cart);
    	$em->flush();
    	return $this->redirect($this->generateUrl('cart'));
    }
    
    /**
     * Update a cart entity.
     *
     * @Route("/cart/{id}/cartupdate", name="cart_update")
     * 
     */
    public function cartupdateAction($id)
    {
    	$request 	= $this->getRequest();
    	$count		= $request->request->get('count')+0;
    	if($count && is_integer($count) && $count > 0){
	    	$cart = $this->getDoctrine()->getRepository('Top10CabinetBundle:Cart')->find($id);
	    	if (!$cart) throw $this->createNotFoundException('Unable to find cart entity.');
	    	$cart->setQuantity($count);
	    	$em = $this->getDoctrine()->getEntityManager();
	    	$em->persist($cart);
	    	$em->flush();
    	}
    	return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * Cart checkout.
     *
     * @Route("/cart/{type}/{factory}/cartcheckout", name="cart_checkout")
     * @method("post")
     */
    public function cartcheckoutAction($type, $factory)
    {
        $mess = "Ваш заказ успешно оформлен. Подтверждение заказа будет отправлено на Ваш e-mail.";
        $error = false;
        $request = $this->getRequest();

        if ($request->request->get('checkout')) {
            $security = $this->get('security.context');
            /** @var  $user User*/
            $user = $security->getToken()->getUser();
            /** @var  $cart ArrayCollection*/
            $cart = $user->getCarts();
            $cart_type = array();

            if (!$cart) {
                throw $this->createNotFoundException('Unable to find cart entitys by user.');
            }

            if ($type == "disk" && $cart->count()) {
				foreach ($cart as $item)
                    /** @var $item Cart */
                    if ($item->getProduct()->getType() == $type && $item->getProduct()->getFactory()->getId() == $factory ) $cart_type[] = $item;

				$urlredirect = array('ordertype' => 'disk');
            }

            if ($type == "tire" && $cart->count()) {
                foreach ($cart as $item)
					if ($item->getProduct()->getType() == $type && $item->getProduct()->getFactory()->getId() == $factory ) $cart_type[] = $item;

				$urlredirect = array('ordertype' => 'tire');
            }

            if (count($cart_type)){
				$sentmail = $request->request->get('sentmail') ? $request->request->get('sentmail') : 0;
				$this->createOrder($cart_type, $type, $factory, $user, $sentmail );
			}
            else {
                $mess = "Ваш заказ уже успешно оформлен.<br />Подтверждение будет отправлено на Ваш e-mail. Спасибо.";
            }
        }
        else {
            $error = "Ошибка оформления заказа. Обратитесь в службу поддержки.";
        }

        if ($error)
            $this->get('session')->getFlashBag()->add('alert-danger', $error);
        else{
            $this->get('session')->getFlashBag()->add('alert-success', $mess);
			return $this->redirect($this->generateUrl('orders', $urlredirect));
		}

        return $this->redirect($this->generateUrl('cart'));
    }

	private function createOrder($cart = false, $type, $factory, User $user, $sentmail=1)
	{
		//Никогда не выбрасывается
        /*todo созаем Таск для тестовой ветки */
		if(!$cart) {
			throw $this->createNotFoundException('Empty cart for add order.');
		}

		$request 	= $this->getRequest();
		$em 		= $this->getDoctrine()->getManager();
		$def_status = $this->container->getParameter('top10_cabinet.status_default');
		$status 	= $em->getRepository('Top10CabinetBundle:Status')->find($def_status);
		$factory 	= $em->getRepository('Top10CabinetBundle:Factory')->find($factory);
		if (!$status) throw $this->createNotFoundException('NO found default status.');

		$order = new Cabinetorder();
		$all_count = 0;

		foreach ($cart as $item){
			$all_count += $item->getPrice() * $item->getQuantity();

			$productsorders = new ProductsOrders();
			$productsorders->setProduct($item->getProduct());
			$productsorders->setCabinetorder($order);
			$productsorders->setQuantity($item->getQuantity());
			//$productsorders->setQuantityaccept($item->getQuantity());
			$productsorders->setPrice($item->getPrice());
			$em->persist($order);
			$em->persist($productsorders);
			$em->flush();

			$em->remove($item);
			$em->flush();
		}

		$order->setDate(new \DateTime('now'));
		$order->setPrice($all_count);
		$order->setUser($user);
		$order->setNew(true);
		$order->setTodelete(false);
		$order->setStatus($status);
		$order->setType($type);
		$order->setFactory($factory);

		$message = $request->request->get('message') ? $request->request->get('message') : "";
		$order->setMessage( $message );

		$order->setSentmail( $sentmail );

		$em->persist($order);
		$em->flush();

		if( $sentmail == 1){
			$newProductsOrders = $em
				->getRepository('Top10CabinetBundle:ProductsOrders')
				->findByCabinetorder($order->getId());

			$body = $this->renderView('Top10CabinetBundle:Mail:NewOrder.html.twig',
				array('order' => $order,
					"products" => $newProductsOrders,
					"type" => $type
				));

			$emailsTo = $this->container->getParameter('top10_cabinet.emails');


			/** @var  $message \Swift_Message */
			$message = \Swift_Message::newInstance()
				->setSubject('Новый заказ – Кабинет Оптовика')
				->setContentType("text/html")
				->setFrom($this->container->getParameter('top10_cabinet.emails.default'))
				->setTo($user->getEmail())
				->setBody($body)
			;

			/** @var  $mailer Swift_Mailer */
			$mailer = $this->get('mailer');
			$failed_mails = array();
			$send_result  = $mailer->send($message, $failed_mails);
			//Менеджеры просили убрать их из расылки новых заказов 
			/*switch ($type) {
				case "disk":
					if ($user->getEmailmanagerDisk()){
						$emailsTo[] = $user->getEmailmanagerDisk();
					}
					break;
				case "tire":
					if($user->getEmailmanagerTier()){
						$emailsTo[] = $user->getEmailmanagerTier();
					}
					break;
			}*/

			foreach($emailsTo as $email){
				$message->setTo($email);
				$send_result  = $mailer->send($message, $failed_mails);
			}
		}

		$this->toFile($order->getId());
	}

	private function createOrderNotCart($products = false, $type, $factory, User $user, $sentmail=1 )
	{
		$error = array();

		//Никогда не выбрасывается
		if(!$type ){
			$error[] = 'not variable type';
			return array( 'order' => false, 'error' => $error );
		}

		if(!$products ){
			$error[] = 'not variable products';
			return array( 'order' => false, 'error' => $error );
		}

		$request 	= $this->getRequest();
		$em 		= $this->getDoctrine()->getManager();
		$def_status = $this->container->getParameter('top10_cabinet.status_default');
		$repProduct	= $em->getRepository('Top10CabinetBundle:Product');

		$repFactory = $em->getRepository('Top10CabinetBundle:Factory');
		$status 	= $em->getRepository('Top10CabinetBundle:Status')->find($def_status);

		if ( $factory )
			$factory = $repFactory->find($factory);
		else{
			if ( $type == 'tire' )
				$factory = $repFactory->findOneBySapid('1401');
			elseif ( $type == 'disk' )
				$factory = $repFactory->findOneBySapid('4101');
			else{
				$error[] = 'not a valid variable type';
				return array( 'order' => false, 'error' => $error );
			}
		}

		if (!$status)
			throw $this->createNotFoundException('NO found default status.');

		$order = new Cabinetorder();
		$all_count = 0;
		$product_count = 0;

		if($products) {
			//$item = $products;
			foreach ($products as $item){
				$product = $repProduct->findOneBy(array( 'article' => $item['article'] ));

				if( $product  ){
					if( $product->getType() == $type ){
						if( $item['quantity'] > 0 && $product->getQuantity() > 0 && $product->getApproved() == 1 ){
							$price = $product->getPriceForUser($user);

							$all_count += $price * $item['quantity'];

							$productsorders = new ProductsOrders();
							$productsorders->setProduct( $product );
							$productsorders->setCabinetorder($order);
							$productsorders->setQuantity($item['quantity']);
							//$productsorders->setQuantityaccept($item->getQuantity());
							$productsorders->setPrice($price);
							$em->persist($order);
							$em->persist($productsorders);
							$em->flush();
							$product_count++;
						}
						else
							$error[] = 'not a valid variables quantity ' . $item['quantity'] . ' in product ' . $item['article'];
					}
					else 
						$error[] = 'not a valid variables type ' . $product->getType() . ' in product ' . $item['article'];
				}
				else
					$error[] = 'not a valid variables article ' . $item['article'];
			}
		}

		if( $product_count == 0 ){
			$error[] = 'not a products in order';
			return array( 'order' => false, 'error' => $error );
		}

		$order->setDate(new \DateTime('now'));
		$order->setPrice($all_count);
		$order->setUser($user);
		$order->setNew(true);
		$order->setTodelete(false);
		$order->setStatus($status);
		$order->setType($type);
		$order->setFactory($factory);

		$message = $request->request->get('message') ? $request->request->get('message') : "API";
		$order->setMessage( $message );

		$order->setSentmail( $sentmail );

		$em->persist($order);
		$em->flush();

		if( $sentmail == 1){
			$newProductsOrders = $em
				->getRepository('Top10CabinetBundle:ProductsOrders')
				->findByCabinetorder($order->getId());

			$body = $this->renderView('Top10CabinetBundle:Mail:NewOrder.html.twig',
				array('order' => $order,
					"products" => $newProductsOrders,
					"type" => $type
				));

			$emailsTo = $this->container->getParameter('top10_cabinet.emails');


			/** @var  $message \Swift_Message */
			$message = \Swift_Message::newInstance()
				->setSubject('Новый заказ – Кабинет Оптовика')
				->setContentType("text/html")
				->setFrom($this->container->getParameter('top10_cabinet.emails.default'))
				->setTo($user->getEmail())
				->setBody($body)
			;

			/** @var  $mailer Swift_Mailer */
			$mailer = $this->get('mailer');
			$failed_mails = array();
			$send_result  = $mailer->send($message, $failed_mails);

			foreach($emailsTo as $email){
				$message->setTo($email);
				$send_result  = $mailer->send($message, $failed_mails);
			}
		}

		$this->toFile($order->getId());

		return array( 
			'order' => $order,
			'error' => $error
		);
	}


	/**
	 * Добавляем данные по заказу в файл
	 * 
	 */
	private function toFile($id, $action = "new", $mailinvoice = "0"){
		
		if(!$id) return;
		
		$logger = $this->get('logger');
		
		if($action == "delete"){
			unlink("../var/ko/2/".$id.".json");
			$logger->info(
				sprintf('[2.JSON] DELETE order: %s', $id)
			);
			return;
		}
		
		$em 	  	= $this->getDoctrine()->getEntityManager();
		$neworder 	= $em->getRepository('Top10CabinetBundle:Cabinetorder')->findOneById($id);
		$newProductsOrders = $em->getRepository('Top10CabinetBundle:ProductsOrders')
								->findByCabinetorder($id);

		$jsproducts = array();
		foreach ($newProductsOrders as $product){
			$jsproducts[] = array(
				$product->getProduct()->getArticle(),
				$product->getQuantity()*1,
//				$product->getPrice()
			);
		}
		$userjson = array(
			"Id" 		=> $neworder->getId(),
			"Sapid"		=> $neworder->getSapid(),
			"Factory"	=> $neworder->getFactory()->getSapid(),
			"Date" 		=> $neworder->getDate()->format("d.m.Y"),
			//"Price" 	=> $neworder->getPrice(),
			"Usersapid" => str_pad($neworder->getUser()->getSapid(), 6, "0", STR_PAD_LEFT),
			"Message"	=> $neworder->getMessage(),
			"Type"		=> ($neworder->getType() == "disk" ? "41" : "14"),
			"Gpsb"		=> ($neworder->getType() == "disk" ? "80" : $neworder->getUser()->getGpsbTire()),
			"Mail"		=> $mailinvoice,
			"Products" 	=> $jsproducts
		);

		$logger->info(
			sprintf('[2.JSON] '.($action == "new" ? "New" : "Update").' order: %s', $neworder->getId())
		);

		$json_str = json_encode($userjson, JSON_UNESCAPED_UNICODE);
		$json_str = preg_replace('/\"Usersapid\":\"(\d+)\"/','"Usersapid":$1',$json_str);

		file_put_contents("../var/ko/2/".$neworder->getId().".json", $json_str);
		file_put_contents("../var/logfile/ko/2/".$neworder->getId().".json", $json_str);
		return;
	}

    /**
     * Orders list.
     *
     * @Route("/orders/{ordertype}", name="orders")
     * @Template("Top10CabinetBundle:Default:orders.html.twig")
     */
    public function ordersAction( $ordertype, Request $request)
    {
        $security = $this->get('security.context');
        /** @var $user Entity\User */
        $user	  = $security->getToken()->getUser();
		$em 	  = $this->getDoctrine()->getManager();
		$env	  = $this->get('kernel')->getEnvironment();

		/** @var $orderRepo EntityRepository */
        $orderRepo = $em->getRepository('Top10CabinetBundle:Cabinetorder');

        $filter = new \Top10\CabinetBundle\Form\Model\OrderFilter();
        $filter_order_form = $this->createForm(new OrderFilterForm(), $filter);
        $filter_order_form->bind($request);

        $qb = $orderRepo->createQueryBuilder('o');
        $qb
            ->addSelect('o')
            ->andWhere('o.user = :user')
                ->setParameter('user', $user)
            ->andWhere('o.todelete = 0')
            ->addOrderBy('o.created', 'DESC')
        ;

		if( $ordertype == 'disk' )
			$qb->andWhere('o.type = :ordertype')
				->setParameter('ordertype', $ordertype);

		if( $ordertype == 'tire' )
			$qb->andWhere('o.type = :ordertype')
				->setParameter('ordertype', $ordertype);

        if( $filter->getDateFrom() !== null || $filter->getDateTo() !== null ) {
            $qb->andWhere('o.status = 6');
        }
        else {
            $qb->andWhere('o.status <> 6');
        }

        if( $filter->getDateFrom() !== null ) {
            $qb->andWhere('o.date >= :dateFrom')->setParameter("dateFrom", $filter->getDateFrom()->format("Y-m-d"));
        }
        if( $filter->getDateTo() !== null ) {
            $qb->andWhere('o.date <= :dateTo')->setParameter("dateTo", $filter->getDateTo()->format("Y-m-d"));
        }

        $paginator = $this->get('knp_paginator');
        
		//if( $user->getId() == 100000 ) {
			$oldOrderQb = clone $qb;
			$oldOrderQb
				->andWhere('o.status NOT IN (:status)')
					->setParameter( "status", array(5,6,7) )
				->andWhere('o.created < :threeday')
					->setParameter( "threeday", date( 'Y.m.d H:i:s', mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")) ) );
			$oldOrders = $oldOrderQb->getQuery()->getArrayResult();

			if( $oldOrders ){
				$repStatus = $em->getRepository('Top10CabinetBundle:Status');
				$statusDelete = $repStatus->find( 7 );
				
				foreach( $oldOrders as $oldOrderArr ){
					$oldOrder = $orderRepo->find( $oldOrderArr['id'] );
					$oldOrder->setStatus( $statusDelete );
					$em->persist($oldOrder);
				}
				$em->flush();
			}
		//}

        $pagination = $paginator->paginate( $qb, $this->get('request')->query->get('page', 1), 20 );

        //$tirePagination = $paginator->paginate( $tireQb, 1, 1000 );

        $result = array(
            'pagination' => $pagination,
            'ordertype' => $ordertype,
            //'tirePagination' => $tirePagination,
            'filter_order_form' => $filter_order_form->createView(),
        );

        /** @var $cartObj CartManager */
        $cartObj = $this->get('cabinet.cart_manager');
        $cart = $cartObj->getUserCartInfo();
        $result['cartinfo'] = $cart;

        return $result;
    }


	/**
     * Edit a order.
     *
     * @Route("/order/{id}/sentmail", name="sentmail")
     */
    public function orderSentMailAction($id, Request $request)
    {

		$request 	= $this->getRequest();
		$em 			  = $this->getDoctrine()->getManager();
		$rep			  = $em->getRepository('Top10CabinetBundle:Cabinetorder');
		$order = $rep->find($id);

		$sentmail = $request->request->get('sentmail') ? $request->request->get('sentmail') : 0;
		$order->setSentmail( $sentmail );
		$em->persist($order);
		$em->flush();

		//return;//$this->redirect( $this->generateUrl( 'order_edit', array("id" => $id) ) );
		return new Response(
            $order->getSentmail()
        );

	}
	
    /**
     * Edit a order.
     *
     * @Route("/order/{id}/edit", name="order_edit")
     * @Template("Top10CabinetBundle:Default:editorder.html.twig")
     */
    public function ordereditAction($id, Request $request)
    {
    	$security		  = $this->get('security.context');
    	$current_user 	  = $security->getToken()->getUser();
        $em 			  = $this->getDoctrine()->getManager();
		$rep			  = $em->getRepository('Top10CabinetBundle:Cabinetorder');
		$env			  = $this->get('kernel')->getEnvironment();
		$disabledDelivery = false;
		$result 		  = array();
		$j3importer 	  = $this->get('cabinet.json3_import');//берем функцию для отправки почты

        /** @var $order Cabinetorder */
        $order = $rep->find($id);

        if( !$order || $order->getUser() !== $current_user ) {
    		throw $this->createNotFoundException('Not found order entity.');
    	}

		$datedelorder = clone $order->getCreated();
		$datedelorder->modify(' + 3 days');


		//if( $env == "dev" ) {
		if( $current_user->getId() == "23" ) {

			if( is_object( $order->getCreated() ) &&  $datedelorder->format( 'Y-m-d' ) < date('Y-m-d') && $order->getStatus()->getId() != 5 && $order->getStatus()->getId() != 6 ){
				$this->get('session')->getFlashBag()->add('alert-danger', "Заказы созданные более <strong>3-х дней</strong> назад редактировать запрещено");
				$disabledDelivery = true;
			}

			if( $order->getSupply() )
				if( $order->getSupply()->getStatussupply() && ( $order->getSupply()->getStatussupply()->getSapid() == 1 || $order->getSupply()->getStatussupply()->getSapid() == 98 ) ) 
					$disabledDelivery = true;

			if($order->getStatus()->getId() == 7 || $order->getStatus()->getId() == 6 || $order->getStatus()->getId() == 5) {
				$this->get('session')->getFlashBag()->add('alert-danger', "Заказы со статусом <strong>" . $order->getStatus()->getName() . "</strong> редактировать запрещено");
				$disabledDelivery = true;
			}

			if($order->getSapid() == null && $order->getStatus()->getId() != 4 && $order->getStatus()->getId() != 1) {
				$this->get('session')->getFlashBag()->add('alert-danger', "Заказу не присвоен номер");
				$disabledDelivery = true;
			}

			/*выбор типа доставки
			$deliverytypesRequest = null;
			if ($request->request->get('top10_cabinetbundle_supply')['isdeliverytype'] == '1'){
				$deliverytypesRequest = $request->request->get('top10_cabinetbundle_supply')['deliverytypeint'];
			}*/


//-----------------ДОСТАВКА И ПОСТАВКА---------------------
			if( $order->getStatus()->getId() == 2 || $order->getStatus()->getId() == 3 || $order->getStatus()->getId() == 5 || $order->getStatus()->getId() == 6 )
			{
				if(is_object( $order->getCreated() ) && $datedelorder->format( 'Y-m-d' ) >= date('Y-m-d') )
				{
					/*$this->get('session')->getFlashBag()->add('alert alert-success alert-dismissable alert-top', "
					<strong>Внимание!</strong> Если вы готовы купить этот заказ. Вы можете подать заявку на сбор вашего груза.
					<ul>
						<li>Укажите параметры доставки в разделе <a href='#supply'><strong>Заявить заказ на поставку</strong></a> и дождитесь подтверждения статуса.</li>
						<li>Если статус заявки будет <strong>Согласовано с ФинОтделом</strong> то ваш заказ будет отправлен на сборку.</li>
						<li>Если статус заявки будет иным, то, как и раньше, согласуйте поставку с менеджером.</li>
					</ul>
					");*/
					if( !$order->getSupply() )
						$this->get('session')->getFlashBag()->add('alert alert-success alert-dismissable alert-top', "<strong>Внимание!</strong> Если вы готовы купить этот заказ. Вы можете подать заявку на сбор вашего груза.<ul><li>Укажите параметры доставки в разделе <a href='#supply'><strong>Заявить заказ на поставку</strong></a> и нажмите <strong>Заявить на поставку</strong></li><li>После чего будет отправленно письмо вашему менеджеру с информацией о Поставке</li></ul>");


					if( $order->getSupply() )
						$supply = $order->getSupply();
					else
						$supply = new supply();

						$deliverytypes = $em
						   ->getRepository('Top10CabinetBundle:Deliverytype')
						   ->createQueryBuilder('d')
						   ->select('d')
						   ->getQuery()
						   ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

						$deliverytypesRequest = $request->request->get('top10_cabinetbundle_supply')['deliverytypeint'];

						//формируем форму
						$supplyForm = $this->createForm(new SupplyForm(
																$order, // данные заказа и пользователя для всавки в инпуты по умолчанию 
																$deliverytypes, // список типов доставки для вставки в select
																$disabledDelivery, //визуальный запрет на редактирование инпутов
																$request->request->get('top10_cabinetbundle_supply')['deliverytypeint'] //тип доставки, чтобы исключить/добавить инпуты
															),
														$supply
														);

						$supplyForm->bind( $request );

						if ( $request->isMethod('POST') /*&& $supplyForm->isValid()*/ ) {
							if ( count( $request->request->get('items') ) > 0 ) {
								if( $disabledDelivery == true ) {
									if( $order->getSupply() )
										$this->get('session')->getFlashBag()->add('alert-danger', "Заказы со статусом поставки '" . $order->getSupply()->getStatussupply()->getName() . "' редактировать запрещено");
								}
								else{
									if( $request->request->get('top10_cabinetbundle_supply')['isdeliverytype'] == null ) {

										if( $supplyForm->get('deliverytypeint')->getData() != null ){
											$deliverytype = $em->getRepository('Top10CabinetBundle:Deliverytype')->find( $supplyForm->get('deliverytypeint')->getData() );
											$supply->setDeliverytype( $deliverytype );
										}

										$status_supply_default = $this->container->getParameter('top10_cabinet.status_supply_default');
										$status_supply = $em->getRepository('Top10CabinetBundle:Statussupply')->find( $status_supply_default );

										if (!$status_supply)
											throw $this->createNotFoundException('NO found default status.');

										$supply->setCabinetorder( $order );
										$supply->setStatussupply( $status_supply );

										$em->persist( $supply );

										//$em->persist($order);
										$em->flush();

										//$msg = $this->toFileSupply($id, "update");

										$this->supplyProductAction( $order->getId(), $supply->getId() );

										$this->get('session')->getFlashBag()->add('alert-success', "Заявка на <strong>Отгрузку</strong> отправлена вашему менеджеру, вскоре он свяжется с вами");

										return $this->redirect( $this->generateUrl( 'order_edit', array("id" => $id) ) );

										//$j3importer->sendEmailChangeStatusOrder( $order, 'supply' );
									}
								}
							}
							else
								$this->get('session')->getFlashBag()->add('alert-danger', "Вы не выбрали ни одной позиции для поставки");
						}

						//так как объявление формы впереди сохраниения формы в базу, пишем это условие еше раз
						if( $order->getSupply() )
							if(  is_object( $order->getSupply()->getStatussupply() ) && ( $order->getSupply()->getStatussupply()->getSapid() == 1 || $order->getSupply()->getStatussupply()->getSapid() == 98 ) )
								$disabledDelivery = true;

						$result['supplyForm'] = $supplyForm->createView();
				}
			}
			//else
				//$this->get('session')->getFlashBag()->add('alert-danger', "Нельзя заявить на Поставку заказы со статусом '" . $order->getStatus()->getName() . "'");
		}
//-----------------/ДОСТАВКА И ПОСТАВКА---------------------

		$result['disabledDelivery'] = $disabledDelivery;
		$result['datedelorder'] = $datedelorder;
		$result['order'] = $order;
		$result['supply'] = $order->getSupply();

		$cartObj = $this->get('cabinet.cart_manager');
		$cart = $cartObj->get();
		$result["cartinfo"] = $cart;

		return $result;
    }

	/**
     * action XML a order.
     *
     * @Route("/api/{login}/{password}/{id}/order.xml", defaults={"_format"="xml"}, name="order_xml")
     * @Template()
     */
    public function orderXMLAction($id, $login, $password, Request $request)
    {
    	$security	  = $this->get('security.context');
        $em 		  = $this->getDoctrine()->getManager();
		$rep		  = $em->getRepository('Top10CabinetBundle:Cabinetorder');
		$repUser	  = $em->getRepository('Top10CabinetBundle:User');
		$orderNode	  = new \SimpleXMLElement( "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><order></order>" );


		$user = $repUser->findOneBy(array('emailCanonical' => $login));

		if( $user ){
			$encoder_service = $this->get('security.encoder_factory');
			$encoder = $encoder_service->getEncoder($user);
			$encoded_pass = $encoder->encodePassword($password, $user->getSalt());

			if( $encoded_pass === $user->getPassword() ){
				$order = $rep->find($id);
				
				if( !$order )
					$orderNode = $orderNode->addChild('error', 'not a valid id order');
				else{
					$orderNode->addChild( 'sapid', $order->getSapid() );
					$orderNode->addChild( 'price', $order->getPrice() );
					$orderNode->addChild( 'statusid', $order->getStatus()->getId() );
					$orderNode->addChild( 'status', $order->getStatus()->getName() );

					foreach ( $order->getProductsorders() as $productOrders ) {
						$productNode = $orderNode->addChild('product');

						$productNode->addChild( 'article', $productOrders->getProduct()->getArticle() );
						$productNode->addChild( 'articleexternal', $productOrders->getProduct()->getArticleExternal() );
						$productNode->addChild( 'name', $productOrders->getProduct()->getName() );
						$productNode->addChild( 'quantity', $productOrders->getQuantity() );
						$productNode->addChild( 'quantityaccept', $productOrders->getQuantityaccept() );
						$productNode->addChild( 'price', $productOrders->getPrice() );
					}
				}
			}
			else
				$orderNode = $orderNode->addChild('error', 'not a valid login or password');
		}

		return new Response($orderNode->asXML());
	}

	/**
     * action XML a order.
     *
     * @Route("/api/{login}/{password}/{type}/ordercreate.xml", defaults={"_format"="xml"}, name="createorder_xml")
     * @Template()
     */
    public function orderCheckoutXMLAction( $login, $password, $type, Request $request)
    {
    	$security	= $this->get('security.context');
        $em 		= $this->getDoctrine()->getManager();
		$repUser	= $em->getRepository('Top10CabinetBundle:User');
		$orderNode	= new \SimpleXMLElement( "<?xml version='1.0' encoding='UTF-8' standalone='yes'?><order></order>" );

		$user = $repUser->findOneBy(array('emailCanonical' => $login));

		if( $user ){
			$encoder_service = $this->get('security.encoder_factory');
			$encoder = $encoder_service->getEncoder($user);
			$encoded_pass = $encoder->encodePassword($password, $user->getSalt());
			if( $encoded_pass === $user->getPassword() ){
//$orderNode = $orderNode->addChild('break', $request->query->get('type'));
				$createOrder = $this->createOrderNotCart( $request->request->get('product'), $type, false, $user, 0 );

				foreach ($createOrder['error'] as $error)
					$orderNode->addChild( 'error', $error );

				if( $createOrder['order'] )
					$orderNode->addChild( 'id', $createOrder['order']->getId() );
				else
					$orderNode->addChild('error', 'not order');
			}
			else
				$orderNode->addChild('error', 'not a valid login or password');
			
		}

		return new Response($orderNode->asXML());
	}


	/**
	* Выбирает позиции для поставки и невыбранные добовляет в новый заказ
	*/
	private function supplyProductAction( $order_id, $supply_id )
	{
		$request 			= $this->getRequest();
		$items				= $request->request->get('items');
		$quantityArr		= $request->request->get('quantity', array());
		$em 				= $this->getDoctrine()->getManager();
		$order 				= $em->getRepository('Top10CabinetBundle:Cabinetorder')->find( $order_id );
		$repProductsOrders	= $em->getRepository('Top10CabinetBundle:Productsorders');
		$supply 			= $em->getRepository('Top10CabinetBundle:Supply')->find( $supply_id );

		/** @var  $cart_manager \Top10\CabinetBundle\Service\CartManager */
		$cart_manager = $this->get('cabinet.cart_manager');

		$all_price 		= 0;
		$remnant		= 0;
		$current_user 	= $this->get('security.context')->getToken()->getUser();
		$user 			= $order->getUser();
		$products 		= array();

		if( $current_user->getId() != $user->getId() )
			throw $this->createNotFoundException('NOT RULE FOR USER');

		$def_status = $this->container->getParameter('top10_cabinet.status_default');
		$status 	= $this->getDoctrine()->getRepository('Top10CabinetBundle:Status')->find($def_status);

		foreach ( $order->getProductsorders() as $productsOrders ){
			$productsOrders = $repProductsOrders->find( $productsOrders->getId() );

			if( isset($items[$productsOrders->getId()]) && $items[$productsOrders->getId()] == '1' ){
				$remnant = $productsOrders->getQuantityaccept() - $quantityArr[ $productsOrders->getId() ];

				if( $remnant > 0 )
					$result = $cart_manager->addToCard( $productsOrders->getProduct()->getId(), $remnant );

				if( $remnant <= 0 )
					$quantityArr[ $productsOrders->getId() ] = $productsOrders->getQuantityaccept();

				$productsOrders->setQuantity( $quantityArr[ $productsOrders->getId() ]+0 );
				$all_price = $quantityArr[ $productsOrders->getId() ] * $productsOrders->getPrice();
			}
			else{
				$result = $cart_manager->addToCard( $productsOrders->getProduct()->getId(), $productsOrders->getQuantityaccept() );
				$em->remove($productsOrders);
			}
			$em->flush();
		}

		$supply->setPrice($all_price);
		$em->persist($supply);
		$em->flush();

		$this->toFile($order_id, "update");

		$cart = $user->getCarts();
		if (count($cart))
			$this->createOrder( $cart, $order->getType(), $order->getFactory()->getId(), $user, $order->getSentmail() );
	}


	/**
	 * Group update order
	 *
	 * @Route("/orders/{id}/groupupdate", name="group_update_order")
	 * @Method("post")
	 * 
	 */
	public function groupupdateAction($id)
	{
		$request	= $this->getRequest();
		$items		= $request->request->get('items');
		$mailinvoice= $request->request->get('mailinvoice');
		$em 		= $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository('Top10CabinetBundle:ProductsOrders');
		$order		= $this->getDoctrine()->getRepository('Top10CabinetBundle:Cabinetorder')->find($id);

		$result = array(
			"id" => $id
		);

		$datedelorder = clone $order->getCreated();
		$datedelorder->modify(' + 3 days');

		if( is_object( $order->getCreated() ) &&  $datedelorder->format( 'Y-m-d' ) < date('Y-m-d') && $order->getStatus()->getId() != 5 && $order->getStatus()->getId() != 6 ){
			$this->get('session')->getFlashBag()->add('alert-danger', "Заказы созданные более 3-х дней назад редактировать запрещено");
			return $this->redirect($this->generateUrl('order_edit', $result));
		}

		if($order->getStatus()->getId() == 7 || $order->getStatus()->getId() == 6 || $order->getStatus()->getId() == 5) {
			$this->get('session')->getFlashBag()->add('alert-danger', "Заказы со статусом '" . $order->getStatus()->getName() . "' редактировать запрещено");
			return $this->redirect($this->generateUrl('order_edit', $result));
		}

		if( $order->getSupply() ){
			if( $order->getSupply()->getStatussupply()->getSapid() == 1 || $order->getSupply()->getStatussupply()->getSapid() == 98 ) {
				$this->get('session')->getFlashBag()->add('alert-danger', "Заказы заявленные на поставку со статусом '" . $order->getSupply()->getStatussupply()->getName() . "' редактировать запрещено");
				return $this->redirect($this->generateUrl('order_edit', $result));
			}
		}

		$all_count		= 0;
		$current_user	= $this->get('security.context')->getToken()->getUser();
		$user			= $order->getUser()->getId();
		$products		= array();

		if($current_user->getId() != $user) throw $this->createNotFoundException('NOT RULE FOR USER');

		if( $mailinvoice ){
			$status = $this->getDoctrine()->getRepository('Top10CabinetBundle:Status')->find(8);
		}
		else{
			$def_status = $this->container->getParameter('top10_cabinet.status_default');
			$status = $this->getDoctrine()->getRepository('Top10CabinetBundle:Status')->find($def_status);
		}

		foreach ($items as $item){
			$quantity = $request->request->get('quantity'.$item)+0;
			if(!is_integer($quantity)) continue;
			$products[] = array(
				"id" 	=> $item,
				"quantity" => $quantity
			);
		}

		foreach ($products as $product){
			$productsorders = $repository->findOneBy(array(
				"cabinetorder" 	=> $id,
				"product" 		=> $product["id"]
			));
			if($product["quantity"] > 0){
				$productsorders->setQuantity($product["quantity"]);
				$em->persist($productsorders);
			}else{
				$em->remove($productsorders);
			}
			$em->flush();
		}

		foreach ($order->getProductsorders() as $product){
			$all_count += $product->getQuantity()*$product->getPrice();
		}

		$order->setNew(true);
		$order->setPrice($all_count);
		$order->setStatus($status);
		$em->persist($order);
		$em->flush();

		if( $mailinvoice )
			$this->toFile($id, "update", "1");
		else
			$this->toFile($id, "update");

		$this->get('session')->getFlashBag()->add('alert-success', "Количество товара успешно обновлено");

		return $this->redirect($this->generateUrl('order_edit', $result));
	}
    
    /**
     * Delete a products in order.
     *
     * @Route("/orders/{id}/productorderdelete", name="product_order_delete")
     */
    public function productorderdeleteAction($id)
	{
    	$em = $this->getDoctrine()->getManager();
    	$security = $this->get('security.context');
        /** @var $item ProductsOrders */
        $item = $this->getDoctrine()->getRepository('Top10CabinetBundle:ProductsOrders')->find($id);
		$order = $item->getCabinetorder();

        if( !$item || $order->getUser()->getId() !== $security->getToken()->getUser()->getId() ) {
            $this->createNotFoundException('Not found order entity.');
        }

		$result = array(
			"id" => $order->getId()
		);

        $datedelorder = clone $order->getCreated();
		$datedelorder->modify(' + 3 days');
		if( is_object( $order->getCreated() ) &&  $datedelorder->format( 'Y-m-d' ) < date('Y-m-d') && $order->getStatus()->getId() != 5 && $order->getStatus()->getId() != 6 ){
			$this->get('session')->getFlashBag()->add('alert-danger', "Заказы созданные более <strong>3-х дней</strong> назад редактировать запрещено");
			return $this->redirect($this->generateUrl('order_edit', $result));
		}

		/*if($item->getFlag() == 'noedit') {
            $this->get('session')->getFlashBag()->add('alert-danger', "Товары помеченные флагом '" . $item->getFlag() . "' удалять запрещено");
            return $this->redirect($this->generateUrl('order_edit', $result));
        }*/

    	if($order->getStatus()->getId() == 7 || $order->getStatus()->getId() == 6 || $order->getStatus()->getId() == 5) {
    		$this->get('session')->getFlashBag()->add('alert-danger', "Товары в заказе со статусом '" . $order->getStatus()->getName() . "' удалять запрещено");
    		return $this->redirect($this->generateUrl('order_edit', $result));
    	}

		if( $order->getSupply() ){
			if($order->getSupply()->getStatussupply()->getSapid() == 1 || $order->getSupply()->getStatussupply()->getSapid() == 98) {
				$this->get('session')->getFlashBag()->add('alert-danger', "Товары в заказе заявленные на поставку со статусом '" . $order->getSupply()->getStatussupply()->getName() . "' удалять запрещено");
				return $this->redirect($this->generateUrl('order_edit', $result));
			}
		}


    	$price = $order->getPrice();
    	$order->setPrice( $price - ($item->getQuantity() * $item->getPrice()) );
    	$em->persist($order);
    	$em->flush();
    	
    	$em->remove($item);
    	$em->flush();

		if($item->getFlag() != 'noedit')
			$this->toFile($item->getCabinetorder()->getId(), "update");
    	
    	$this->get('session')->getFlashBag()->add('alert-success', "Товар из заказа успешно удален");
    	
    	return $this->redirect($this->generateUrl('order_edit', array("id" => $order->getId())));
    	 
    }

        /**
     * Delete a order.
     *
     * @Route("/order/{id}/orderdelete", name="order_delete")
     *
     */
    public function orderdeleteAction($id){

    	$item		= array();
    	$security 	= $this->get('security.context');
    	$user  		= $security->getToken()->getUser()->getId();
    	$em 		= $this->getDoctrine()->getManager();
		$order = $item->getCabinetorder();

        $item = $this->getDoctrine()->getRepository('Top10CabinetBundle:ProductsOrders')->find($id);

        if( !$item || $order->getUser()->getId() !== $security->getToken()->getUser()->getId() ) {
            $this->createNotFoundException('Not found order entity.');
        }

        if($item->getFlag() == 'noedit') {
            $this->get('session')->getFlashBag()->add('alert-danger', "Товары помеченные флагом '' удалять запрещено");
			return $this->redirect($this->generateUrl('orders', array( "ordertype" => $order->getType() )));
        }

		if($order->getStatus()->getId() == 7 || $order->getStatus()->getId() == 6 || $order->getStatus()->getId() == 5) {
    		$this->get('session')->getFlashBag()->add('alert-danger', "Заказы со статусом '" . $order->getStatus()->getName() . "' удалять запрещено");
    		return $this->redirect($this->generateUrl('orders', array( "ordertype" => $order->getType() )));
    	}

		if( $order->getSupply() ){
			if($order->getSupply()->getStatussupply()->getSapid() == 1 || $order->getSupply()->getStatussupply()->getSapid() == 98) {
				$this->get('session')->getFlashBag()->add('alert-danger', "Заказы заявленные на поставку со статусом '" . $order->getSupply()->getStatussupply()->getName() . "' удалять запрещено");
				return $this->redirect($this->generateUrl('orders', array( "ordertype" => $order->getType() )));
			}
		}

    	if(is_object($item)){
    		if($item->getSapid()){
    			$item->setTodelete(true);
    			$em->persist($item);
    		}else{
    			$em->remove($item);
    		}
    		$em->flush();
    		$this->get('session')->getFlashBag()->add('alert-success', "Заказ успешно удален");
    	}else{
    		$this->get('session')->getFlashBag()->add('alert-danger', "Произошла ошибка при удалении заказа");
    	}
    	
    	$this->toFile($id, "delete");
    	
    	return $this->redirect($this->generateUrl('orders', array( "ordertype" => $order->getType() )));
    }
    
    /**
     * Add orders on send files for cabinetorder
     *
     * @Route("/orderSendFile/{id}/{type}", name="order_sendFile")
     * 
     */
    public function orderSendFileAction($id, $type){
    	
    	$mess 			= "Ошибка отсылки файла";
    	if($type === "1") $mess = "Накладная будет выслана Вам на email";
    	if($type === "2") $mess = "Счет фактура будет выслана Вам на email";
    	$security 		= $this->get('security.context');
    	$current_user  	= $security->getToken()->getUser()->getId();
    	$doctrine		= $this->getDoctrine();
    	$em 			= $doctrine->getManager();
    	$order 			= $doctrine->getRepository('Top10CabinetBundle:Cabinetorder')->find($id);
    	$order_user 	= $order->getUser()->getId();

    	if($current_user != $order_user) throw $this->createNotFoundException('NOT RULES FOR USER');
    	
    	try{
    		
//     		$fileExist = $doctrine->getRepository('Top10CabinetBundle:File')
//     				->findOneBy(array("cabinetorder" => $id, "type" => $type));
//     		if(!count($fileExist)) {
			if($order->getSapid()){
    			$file = new File();
	    		$file->setCabinetorder($order);
	    		$file->setType($type);
	    		$em->persist($file);
	    		$em->flush();
    		}else{
    			$mess = "Ошибка отсылки файла";
    		}

    	}catch (\Exception $e) {
    		$this->createNotFoundException('Not add orders on send files');
    	}

    	$this->get('session')->getFlashBag()->add('alert-success', $mess);

		return $this->redirect($this->generateUrl('orders', array( "ordertype" => $order->getType() )));

    }
    
    /**
     * file processing xlsx
     * 
     * @Route("fileProcessing", name="fileProcessing")
     * @Secure(roles="ROLE_USER")
     */
    public function fileProcessingAction()
    {
    	$request 	= $this->getRequest();

    	if ( "POST" === $request->getMethod() ){
    		
    		$fileBug 			= $request->files;
            /** @var  $fileUploadedFile  UploadedFile */
    		$fileUploadedFile 	= $fileBug->get("filexlsx");

    		if($fileUploadedFile && $fileUploadedFile->isValid()){
                /** @var  $cart_manager \Top10\CabinetBundle\Service\CartManager */
                $cart_manager = $this->get('cabinet.cart_manager');
    			
    			$inputFileName = $fileUploadedFile->getPathname();
    			
    			$inputFileType 	= \PHPExcel_IOFactory::identify($inputFileName);
				$objReader 		= \PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel 	= $objReader->load($inputFileName);
    			$sheetData 		= $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

                $err_str = null;
                $err_str_article = null;
                $str =  "В корзину не попали следующие товары (неизвестна цена): \n";
                $str_article = "В корзину не попали следующие товары (нет в каталоге): \n";
                /** @var  $session Session */
                $session = $this->get('session');

    			if(count($sheetData)) {
                    /** @var  $repository ProductRepository*/
                    $product_rep = $this->getDoctrine()->getRepository('Top10CabinetBundle:Product');

                    foreach ($sheetData as $dt){

                        $article 	= $dt["A"];
                        $quantity 	= $dt["B"]+0;
						$product = $product_rep->findOneBy( array("article"=>$article, ) );
                        if( !$product ){
                            $err_str_article .= $article . " " . $dt["C"] . "\n";
                            continue;
                        }
//                        var_dump($article);

                        $result = $cart_manager->addToCard($product->getId(), $quantity);

                        if ($result && count($result["wo_price"])) {
                            foreach ($result["wo_price"] as $empty_product) {
                                /** @var $empty_product Product */
                                $err_str .= $empty_product->getArticle() . " " . $empty_product->getName() . "\n";
                            }
                        }
                    }
                }
                $output_flash = null;
                if ($err_str){
                    $output_flash .= $str . $err_str;
                }
                if ($err_str_article){
                    if ($output_flash) {
                        $output_flash .= "\n";
                    }
                    $output_flash .= $str_article . $err_str_article;
                }
                if ($output_flash){
                    $session->getFlashBag()->add('alert-danger', $output_flash);
                }
            }
    	}
    	return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * Add product in order.
     *
     * @Route("/cabinetorder/{id}/add", name="product_order_add")
     * @Method("POST")
     *
     */
    public function productorderaddAction(Cabinetorder $order, Request $request)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
        $orderUserId = $order->getUser()->getId();
        if($currentUser->getId() != $orderUserId) throw $this->createNotFoundException('NOT RULE FOR USER');

		$datedelorder = clone $order->getCreated();
		$datedelorder->modify(' + 3 days');
		if( is_object( $order->getCreated() ) &&  $datedelorder->format( 'Y-m-d' ) < date('Y-m-d') && $order->getStatus()->getId() != 5 && $order->getStatus()->getId() != 6 ){
			$this->get('session')->getFlashBag()->add('alert-danger', "Заказы созданные более 3-х дней назад редактировать запрещено");
			return $this->redirect($this->generateUrl('order_edit', array( "id" => $order->getId() )));
		}

		if( $order->getSupply() )
			if( $order->getSupply()->getStatussupply()->getSapid() == 1 || $order->getSupply()->getStatussupply()->getSapid() == 98 ) {
				$this->get('session')->getFlashBag()->add('alert-danger', "Заказы заявленные на поставку со статусом '" . $order->getSupply()->getStatussupply()->getName() . "' редактировать запрещено");
				return $this->redirect($this->generateUrl('order_edit', array( "id" => $order->getId() )));
			}

        /** @var $em EntityManager */
        $em  = $this->getDoctrine()->getManager();

        /** @var $productRep ProductRepository */
        $productRep = $this->getDoctrine()->getRepository('Top10CabinetBundle:Product');

        $cartids = $request->get('cartid', null);
		$articles = $request->get('article', null);
        $quantitys = $request->get('quantity', null);
        
        //$quantity = (int)$quantity;

        $emptyParameters = array();
        
		foreach ($articles as $article)
			if (($article === null) || ($article == ""))
				$emptyParameters[] = "Укажите артикул товара.";


		foreach ($quantitys as $quantity){
			$quantity = (int)$quantity;
			if (($quantity === null) || ($quantity < 1))
				$emptyParameters[] = "Укажите количество товара.";
		}


        if (count($emptyParameters)>0){
            $this->get('session')->getFlashBag()->add('alert-danger', implode(" ", $emptyParameters));
            return $this->redirect($this->generateUrl('order_edit', array("id" => $order->getId())));
        }

		foreach ($articles as $artKey => $article){

			$product = $productRep->findOneBy(array( 'article' => $article, 'factory' => $order->getFactory()->getId() ));
			//$products = $productRep->findBy(array('article' => $article));

			$orderType = $order->getType();
			$orderTypeText = "";
			switch ($orderType) {
				case "tire":
					$orderTypeText = "шины";
					break;
				case "disk":
					$orderTypeText = "диски";
					break;
			}

			if ($product){
				//$product = current($products);
				$productType = $product->getType();

				$productOrderArticles = array();
				foreach ($order->getProductsorders() as $productOrder)
				{
					$productOrderArticles[] = $productOrder->getProduct()->getArticle();
				}
				if (in_array($product->getArticle(),$productOrderArticles))
				{
					$this->get('session')->getFlashBag()->add('alert-danger', "Товар с таким артикул уже есть в заказе.");
					return $this->redirect($this->generateUrl('order_edit', array("id" => $order->getId())));
				}

				if ($orderType == $productType)
				{
					$price = $product->getPriceForUser($currentUser);
					$whQuantity = $product->getQuantity(); // warehouse Quantity

					if (($price>0) && ($whQuantity>0))
					{
						// добавляем новый товар к заказу
						$productOrder = new ProductsOrders();
						$productOrder->setProduct($product);
						$productOrder->setCabinetorder($order);
						$productOrder->setQuantity( $quantitys[$artKey] );
						$productOrder->setPrice($price);
						//$productOrder->setFlag("edit");
						//$productOrder->setFlag("consid");
						$em->persist($productOrder);
						$em->flush();

						// изменяем заказ
						$defStatus = $this->container->getParameter('top10_cabinet.status_default');
						$status = $this->getDoctrine()->getRepository('Top10CabinetBundle:Status')->find($defStatus);
						$orderPrice = 0;
						foreach ($order->getProductsorders() as $productOrder)
						{
							$orderPrice += $productOrder->getQuantity() * $productOrder->getPrice();
						}
						$order->setNew(true);
						$order->setPrice($orderPrice);
						$order->setStatus($status);

						//----Удаляем из корзины товар-----
						if( $cartids[$artKey] ){
							$cart = $this->getDoctrine()->getRepository('Top10CabinetBundle:Cart')->find( $cartids[$artKey] );
							/*if (!$cart) {
								throw $this->createNotFoundException('Unable to find cart entity.');
							}*/
							//$em = $this->getDoctrine()->getEntityManager();
							if ($cart) {
								$em->remove($cart);
								$em->flush();
							}
						}
						$this->get('session')->getFlashBag()->add('alert-success', "Товар успешно добавлен в заказ. Нажмите кнопку \"Обновить количество\", чтобы товар попал в систему.");

						$this->toFile($order->getId(), "update");
					}

					if ($whQuantity==0)
					{
						$emptyParameters[] = "Товара нет на складе.";
					}
					if ($price == 0)
					{
						$emptyParameters[] = "Товар не имеет цены.";
					}
					if (count($emptyParameters)>0){
						$this->get('session')->getFlashBag()->add('alert-danger', implode(" ",$emptyParameters));
					}
				}
				else
				{
					$this->get('session')->getFlashBag()->add('alert-danger', "В заказ на ".$orderTypeText." Вы можете добавить только ".$orderTypeText);
				}
			}
			else
			{
				$this->get('session')->getFlashBag()->add('alert-danger', "Товар не найден.");
			}
		}

        return $this->redirect($this->generateUrl('order_edit', array("id" => $order->getId())));

    }


	/**
     * Добавляем данные для поставки заказа в файл
     * 
     */
    private function toFileSupply($id, $action = "new")
	{

		if(!$id) return;
		
		$file = '../var/ko/suppko.json';

    	$logger = $this->get('logger');

    	if($action == "delete"){
    		unlink($file);
    		$logger->info(
    			sprintf('[SUPPKO.JSON] DELETE order: %s', $id)
    		);
    		return;
    	}

    	$jsonError = false;
		$em 	  	= $this->getDoctrine()->getEntityManager();
    	$neworder 	= $em->getRepository('Top10CabinetBundle:Cabinetorder')->findOneById($id);

		if ( file_exists($file) ) {

			$fileContent = file_get_contents($file);

			if( $fileContent ){

				$jsonImport =  $this->get('cabinet.json_import');

				$json = $jsonImport->jsonValidate($fileContent, $jsonError, true);

				if ($jsonError) {
					$msg = sprintf('Ошибка в файле: %s', $jsonError);
					//$output->writeln($msg);
					$logger->err($msg);
					$this->get('session')->getFlashBag()->add('alert-danger', $msg);
					return false;
				}

				foreach ($json as $val) {
					if ( $val["Id"] == $neworder->getId() ){
						$msg = sprintf( 'Заявка на поставку уже была отправлена' );
						$logger->err($msg);
						//$output->writeln($msg);
						$this->get('session')->getFlashBag()->add('alert-danger', $msg);
						return false;
					}

				}
			}
		}

    	$json[] = array(
			"Id" 		=> $neworder->getId(),
			"Sapid"		=> $neworder->getSapid(),
			"Date" 		=> $neworder->getDate()->format("d.m.Y"),
		);

		$logger->info(
			sprintf('[SUPPKO.JSON] '.($action == "new" ? "New" : "Update").' order: %s', $neworder->getId())
		);

        $json_str = json_encode($json, JSON_UNESCAPED_UNICODE);
        $json_str = preg_replace('/\"Usersapid\":\"(\d+)\"/','"Usersapid":$1',$json_str);

    	file_put_contents($file, $json_str);
		$this->get('session')->getFlashBag()->add('alert-success', 'Заявка на поставку отправлена');
    	return;
    }

}