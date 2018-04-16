<?php

namespace Top10\CabinetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Top10\CabinetBundle\Entity\Cart;
use Top10\CabinetBundle\Entity\Product;
use Top10\CabinetBundle\Entity\ProductRepository;
use Top10\CabinetBundle\Entity\Cabinetorder;
use Top10\CabinetBundle\Entity\ProductsOrders;
use Top10\CabinetBundle\Entity\User;


class json12Import
{
	//private $user;
	//private $security;
	private $em;
	private $fileTM;

	public function __construct( EntityManager $em)
	{
		//$this->security = $security;
		//$this->user = $security->getToken()->getUser();
		$this->em = $em;
		$this->fileTM = "/var/www/html/cabinet/var/ko/12/";
		//$this->fileTM = "/home/u33250/u33250.netangels.ru/var/ko/12/";
	}






	public function parse()
	{
		$em = $this->em;

		$arrFileTM = scandir( $this->fileTM );
		$arrFileTMJson = array();

		foreach ( $arrFileTM as $fileNameTM ){
			$arrFileNameTM = explode( ".", $fileNameTM );
			if( array_pop( $arrFileNameTM ) == 'json' )
				$arrFileTMJson[] = $this->fileTM . $fileNameTM;
		}

		if( count($arrFileTMJson)>0 )
			foreach ( $arrFileTMJson as $fileNameTMJson ){

				if(!file_exists( $fileNameTMJson )) {
					throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_FOUND);
				}

				$fileContent = file_get_contents( $fileNameTMJson );

				$json = $this->jsonValidate($fileContent, $jsonError);


				$repProduct = $em->getRepository('Top10CabinetBundle:Product');

				$countCartDisks = 0;
		// Диски
				if(is_array($json->Disks)){
					foreach ( $json->Disks as $product ){
						if(!isset($product[0])) continue;

						$product_id = $repProduct->findOneBy(array('article' => $product[0]));
						if (is_object($product_id)){
							$product_id = $product_id->getID();
							$this->addToCard( $product[0], $product[1] );
							$result .= ' -Диски ' . $product[0] . ' В корзине; ';
							$countCartDisks++;
						}
						else{
							$result .= " -Диски" . $product[0] . " Не найдена; ";
						}
					}
					$result .=  "********** в файле Дисков " . count( $json->Disks ) . '; в корзине Дисков ' . $countCartDisks . '; ';
				}
				$countCartTires = 0;
		// Шины
				if(is_array($json->Tires)){
					foreach ( $json->Tires as $product ){
						if(!isset($product[0])) continue;

						$product_id = $repProduct->findOneBy(array('article' => $product[0]));
						if (is_object($product_id)){
							$product_id = $product_id->getID();
							$this->addToCard( $product[0], $product[1] );
							
							$result .= ' -Шина ' . $product[0] . ' В корзине; ';
							$countCartTires++;
						}
						else{
							$result .= " -Шина" . $product[0] . " Не найдена; ";
						}
					}
					$result .=  " В файле Шин " . count( $json->Tires ) . '; в корзине Шин ' . $countCartTires . '; ***********';
				}
				//}
				if ( $countCartDisks > 0 ){
					$orderResult = $this->cartcheckoutAction( 'disk', $json->idOrderTm );
					$result .= $orderResult;
				}

				if ( $countCartTires > 0 ){
					$orderResult = $this->cartcheckoutAction( 'tire', $json->idOrderTm );
					$result .= $orderResult;
				}
				unlink( $fileNameTMJson );
			}
		else
			$result = 'файлов нет';

		return $result;
	}



	public function jsonValidate($string, &$error = null)
    {
        // отрезаем BOM
        $bom = mb_substr($string, 0, 3);
        if ($bom === "\xEF\xBB\xBF") {
            $string = mb_substr($string, 3);
        }

        $json = json_decode($string);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = false;
                break;
            case JSON_ERROR_DEPTH:
                $error =  'Достигнута максимальная глубина стека';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error =  'Некорректные разряды или не совпадение режимов';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error =  'Некорректный управляющий символ';
                break;
            case JSON_ERROR_SYNTAX:
                $error =  'Синтаксическая ошибка, некорректный JSON';
                break;
            case JSON_ERROR_UTF8:
                $error =  'Некорректные символы UTF-8, возможно неверная кодировка';
                break;
            default:
                $error =  'Неизвестная ошибка';
                break;
        }

        return $error ? null: $json;
    }






	public function addToCard($id, $count)
    {
        $em = $this->em;

		$repUser = $em->getRepository('Top10CabinetBundle:User');

		$user = $repUser->findOneById( 23 );

        /** @var  $repository ProductRepository */
		$repository = $em->getRepository('Top10CabinetBundle:Product');

        /** @var  $repCart \Doctrine\ORM\EntityRepository */
        $repCart = $em->getRepository('Top10CabinetBundle:Cart');

        $count = (int) $count;
        /** @var  $product Product */
        $product = $repository->findOneByArticle($id);

        if( $count <= 0 || $product === null ) {
            return null;
        }

        //$price = $product->getPriceForUser($this->user);
        //if( $price > 0 ) {
            /** @var $cart Cart */
            $cart = $repCart->findOneBy( array(
                'product' => $product->getId(),
                'user' => 23,//$this->user->getId(),
            ));

            if( $cart !== null ) {
                //$cart->setPrice($price);
                $cart->setPrice( $product->getprice03() );
                $cart->setQuantity($cart->getQuantity() + $count);
            }
            else {
                $cart = new Cart();
                $cart->setUser($user);
                $cart->setProduct($product);
                $cart->setPrice( $product->getprice03() );
                $cart->setQuantity($count);
                $em->persist($cart);
            }

            $em->flush();

            //$result["added"][] = $product;

        /*}
        else { // Наполняем масив товаров у которых не оказалось цены.
            $result["wo_price"][] = $product;
        }*/

        return true;
    }


	public function cartcheckoutAction( $type, $idOrderTm )
    {
		$em = $this->em;

		$mess = " ЗАКАЗ ОФОРМЛЕН. ";
		$error = false;
		/** @var  $user User*/

		$repUser = $em->getRepository('Top10CabinetBundle:User');
		$user = $repUser->findOneById( 23 );

			/** @var  $cart ArrayCollection*/
			$cart = $user->getCarts();
			$cart_type = array();

			/*if (!$cart) {
				throw $this->createNotFoundException('Unable to find cart entitys by user.');
			}*/
			if ($type == "disk" && $cart->count()) {
				foreach ($cart as $item){
					if ($item->getProduct()->getType() == $type) $cart_type[] = $item;
				}
			}

			if ($type == "tire" && $cart->count()) {
				foreach ($cart as $item){
					if ($item->getProduct()->getType() == $type){
						$cart_type[] = $item;
					}
				}
			}

			if (count($cart_type)) {
			   $messFile = $this->createOrder( $cart_type, $type, $user, $idOrderTm );
			   $mess .= $messFile;

			}
			else {
				$mess = " ЗАКАЗ УЖЕ БЫЛ ОФОРМЛЕН. ";
			}       

		return $mess;
    }


	private function createOrder( $cart = false, $type, User $user, $idOrderTm ){

        //Никогда не выбрасывается
        /*if(!$cart) {
            throw $this->createNotFoundException('Empty cart for add order.');
        }*/

        //$request 	= $this->getRequest();
        $em = $this->em;
        $status 	= $em->getRepository('Top10CabinetBundle:Status')->find( 1 );

		if( $type == 'tire' )
			$factory = '1401';
		else
			$factory = '4101';

		$factoryObject = $em->getRepository('Top10CabinetBundle:Factory')->findOneBySapid($factory);
        //if (!$status) throw $this->createNotFoundException('NO found default status.');

        $order = new Cabinetorder();
        $all_count = 0;
        foreach ($cart as $item){
            $all_count += $item->getPrice()*$item->getQuantity();

            $productsorders = new ProductsOrders();
            $productsorders->setProduct($item->getProduct());
            $productsorders->setCabinetorder($order);
            $productsorders->setQuantity($item->getQuantity());
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
        $order->setNew( true );
        $order->setTodelete(false);
        $order->setStatus($status);
        $order->setType($type);
        $order->setFactory($factoryObject);
        $order->setMessage( 'АвтоРезерв Точка-маркет' );
        $order->setIdOrderTm( $idOrderTm );

        $em->persist($order);
        $em->flush();

	return $this->toFile($order->getId());
    }



	private function toFile($id, $action = "new"){

    	if(!$id) return;

    	/*$container = $this->getContainer();
		$logger = $container->get('logger');*/

    	if($action == "delete"){
    		unlink("../var/ko/2/".$id.".json");
    		/*$logger->info(
    			sprintf('[2.JSON] DELETE order: %s', $id)
    		);*/
    		return;
    	}

		$em = $this->em;
    	$neworder 	= $em->getRepository('Top10CabinetBundle:Cabinetorder')->findOneById($id);
    	$newProductsOrders = $em->getRepository('Top10CabinetBundle:ProductsOrders')
								->findByCabinetorder($id);
    	
    	$jsproducts = array();
    	foreach ($newProductsOrders as $product){
    		$jsproducts[] = array(
    			$product->getProduct()->getArticle(),
    			$product->getQuantity(),
//     			$product->getPrice()
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
    			"Mail"		=> "0",
				"Products" 	=> $jsproducts
    	);

		/*$logger->info(
			sprintf('[2.JSON] '.($action == "new" ? "New" : "Update").' order: %s', $neworder->getId())
		);*/

        $json_str = json_encode($userjson, JSON_UNESCAPED_UNICODE);
        $json_str = preg_replace('/\"Usersapid\":\"(\d+)\"/','"Usersapid":$1',$json_str);

    	if( file_put_contents("/var/www/html/cabinet/var/ko/2/".$neworder->getId().".json", $json_str) == false )
			return ' ОШИБКА ФАЙЛА /var/ko/2';
		else{
			return ' ФАЙЛ В ПАПКЕ /var/ko/2 СОЗДАН ';
		}
    }


	function TesterFunction()
	{
		return 1;
	}
}