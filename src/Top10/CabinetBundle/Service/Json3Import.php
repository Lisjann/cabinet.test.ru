<?php

namespace Top10\CabinetBundle\Service;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Top10\CabinetBundle\Classes\JsonImportException;
use Doctrine\ORM\EntityManager;
use Top10\CabinetBundle\Entity\ProductsOrders;
use Monolog\Logger;
use Top10\CabinetBundle\Service\JsonImport;
use Symfony\Component\HttpKernel\Kernel;

class Json3Import extends JsonImport
{
	protected $em;

	public function __construct(EntityManager $em, Logger $logger, Kernel $kernel)
	{
		parent::__construct( $kernel, $logger, 'var/sap/3.json' );
		$this->em = $em;
	}

    /**
     * Обработка файла
     *
     * @throws \Top10\CabinetBundle\Classes\JsonImportException
     * @return array
     */
    public function parse()
    {
    	$messages = array();
    	$result = array(
            'result'	=> false,
    		'messages'	=> $messages
        );

    	if(!file_exists($this->file)) {
    		throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_FOUND);
    	}
    	$sap5 = $this->jsondir."sap5";
    	if(file_exists($sap5)) {
    		throw new JsonImportException(JsonImportException::ERROR_JSON_BLOCKED_SAP5);
    	}
    	$em = $this->em;
    	$kernel = $this->kernel;
    	$env = $kernel->getEnvironment();

    	$fileContent = file_get_contents($this->file);
    	$json = $this->jsonValidate($fileContent, $jsonError);
    	if($jsonError) {
    		throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_VALID,$jsonError);
    	}

    	if (!is_array($json)){
    		throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_ARRAY);
    	}

    	$repCabinetorder = $em->getRepository('Top10CabinetBundle:Cabinetorder');
    	$repProductOrder = $em->getRepository('Top10CabinetBundle:ProductsOrders');
    	$repProduct 	 = $em->getRepository('Top10CabinetBundle:Product');
    	$repStatus		 = $em->getRepository('Top10CabinetBundle:Status');
		$no_id_tovar	 = 0;
    	$no_found_tovar_by_id = 0;
    	$success_update	= 0;
        $ch_st_orders = array();
        $change_status_order = false;

		$result12json = false;
		$json12 = $json;

    	foreach ($json as $key => $jsn){

    		if(!isset($jsn->Id)){
    			$no_id_tovar++;
    			continue;
    		}
    		$order = $repCabinetorder->find($jsn->Id);
            $change_status_order = false;
    		if(is_object($order)){

				//-------------------------------Для Точки-маркет-------------------------------
				if ( $order->getUser()->getId() == 23 ){
					$json12[$key]->IdOrderTm = $order->getIdOrderTm();
					$result12json = true;
				}
				else
					unset ($json12[$key]);
				//-------------------------------/Для Точки-маркет-------------------------------

				if(isset($jsn->Sapid)) $order->setSapid($jsn->Sapid);
				if(isset($jsn->Price)) $order->setPrice($jsn->Price);
				if(isset($jsn->Status)){
					if ($jsn->Status != $order->getStatus()->getId()) $change_status_order = true;
					$status = $repStatus->find($jsn->Status);
					if(is_object($status)) $order->setStatus($status);
				}

				$arrFlag = array();
				if(is_array($jsn->Products)){
					foreach ($jsn->Products as $product){
						if(!isset($product[0]))
							continue;

						$productObj = $repProduct->findOneBy(array('article' => $product[0], 'factory' => $order->getFactory()->getId() ));

						if (is_object($productObj))
						{
							$product_id = $productObj->getID();

							$productsorders = $repProductOrder->findOneBy( array( 'product' => $product_id, 'cabinetorder' => $jsn->Id) );

							if (!$productsorders){
								$productsorders = new ProductsOrders();
								$productsorders->setProduct( $productObj );
								$productsorders->setCabinetorder( $order );
								$productsorders->setAddsap(true);
								$productsorders->setQuantity($product[1]);
								$messages[] = "Не найден заказ с id товара: ".$product_id."; номер заказа: ".$jsn->Id;
							}

							if($product[1]){
								$productsorders->setQuantityaccept($product[1]);
							}
							if($product[2]){
								if( $productsorders->getQuantity() > $product[1] )
									$productsorders->setFlag('part');
								else
									$productsorders->setFlag( $product[2] );
							}
							if($product[3]) $productsorders->setPrice($product[3]);
							$em->persist($productsorders);
							$em->flush();

							if( $product[2] )
								$arrFlag[] = $productsorders->getId();

						}
						else{
							$messages[] = "Не найден артикул товара: ".$product[0];
						}
					}
				}
				//----При статусе Отменен(4) у вcех товаров кроме пришедших в 3.json ставим Flag = del--------
				if( $jsn->Status == 4 || $jsn->Status == 3 || $jsn->Status == 2 ){

					$qbPO = $em->createQueryBuilder();
					$q = $qbPO->update('Top10CabinetBundle:ProductsOrders', 'po')
						->set('po.flag',':flag')
							->setParameter(':flag', 'del')
						->set('po.quantityaccept',':quantityaccept')
							->setParameter(':quantityaccept', '0')
						->set('po.addsap',':addsap')
							->setParameter(':addsap', '1')
						->where( 'po.cabinetorder = :cabinetorder' )
							->setParameter( 'cabinetorder', $jsn->Id );

					if( count($arrFlag) > 0 ){
						$q->andWhere( $qbPO->expr()->notIn('po.id', $arrFlag) );
					}

					$q->getQuery()->execute();


					/*$qbPO = $repProductOrder->createQueryBuilder('po');
					$qbPO->where( 'po.cabinetorder = :cabinetorder' )
						->setParameter( 'cabinetorder', $jsn->Id );

					if( is_array($jsn->Products) && count($arrFlag) > 0 )
						$qbPO->andWhere( $qbPO->expr()->notIn('po.id', $arrFlag) );

					$arrPO = $qbPO->getQuery()->getArrayResult();

					foreach ($arrPO as $PO){
						$productsorders = $repProductOrder->find( $PO['id'] );
						$productsorders->setFlag('del');
						$productsorders->setQuantityaccept(0);
						$em->persist($productsorders);
						$em->flush();
					}*/
				}
				//----/При статусе Отменен(4) у вcех товаров кроме пришедших в 3.json ставим Flag = del--------

				$order->setNew(false);
				$em->persist($order);
				$em->flush();

				//---------------------------запускаем Поставку---------------------------
				if( $jsn->Status == 2 || $jsn->Status == 3 )
					if( $order->getSupply() )
						if( $order->getSupply()->getSapid() == null )
							$this->toFileSupply( $order->getId(), "update" );
				//---------------------------запускаем Поставку---------------------------

				$success_update++;
				if ($change_status_order){
					$ch_st_orders[] = $order; // массив заказов у которых изменился статус
				}
			}else{
				$no_found_tovar_by_id++;
			}
		}

		//---------------------------Для Точки-маркет------------------------
		if ( $result12json == true ){
			$json_str = json_encode($json12);
			$json_str = preg_replace('/\"Usersapid\":\"(\d+)\"/','"Usersapid":$1', $json_str);

			//file_put_contents("/home/u33250/u33250.netangels.ru/var/sap/12.json", $json_str);
			file_put_contents("var/sap/12.json", $json_str);



			$ftpServer = "193.107.237.63";
			$ftpUser = "import";
			$ftpPass = "uqDA73np";
			$fileTM = '/12.json';

			// установка соединения
			$connId = ftp_connect( $ftpServer );
			// проверка имени пользователя и пароля
			$loginResult = ftp_login($connId, $ftpUser, $ftpPass);
			// загрузка файла 
			//if ( ftp_put( $connId, $fileKO, "/home/u33250/u33250.netangels.ru/var/sap/12.json", FTP_BINARY ) ) {
			if ( ftp_put( $connId, $fileTM, "var/sap/12.json", FTP_BINARY ) ) {
				unlink( "var/sap/12.json" );
			}
		}
		/**********************Для Точки-маркет***********************/


    	$message = "Всего в файле заказов: ".count($json)."; Заказов обновлено: ".$success_update;
    	$message.= "; Не найдено: ".$no_found_tovar_by_id."; Заказов без ID: ".$no_id_tovar;
    	$messages[] = $message;
    	$result = array('result'	=> true,
    					'messages'	=> $messages,
    					'all'		=> count($json),
    					'updated'	=> $success_update,
    					'no_found_tovar_by_id'	=> $no_found_tovar_by_id,
    					'no_id_tovar' => $no_id_tovar,
                        'change_status_order' => $change_status_order,
                        'ch_st_orders' => $ch_st_orders);

    	return $result;
    }

	/**
     * Добавляем данные для поставки заказа в файл
     * 
     */
    private function toFileSupply($id, $action = "new")
	{
		if(!$id) return;

		$file = 'var/ko/suppko.json';
		//$file = 'n:\home\cabinet.test.ru\var\ko\suppko.json';

    	if($action == "delete"){
    		unlink($file);
    		$this->logger->info(
    			sprintf('[SUPPKO.JSON] DELETE order: %s', $id)
    		);
    		return;
    	}

    	$jsonError = false;
    	$neworder  = $this->em->getRepository('Top10CabinetBundle:Cabinetorder')->find($id);

		if ( file_exists($file) ) {

			$fileContent = file_get_contents($file);

			if( $fileContent ){

				$json = $this->jsonValidate($fileContent, $jsonError, true);

				if ($jsonError) {
					$msg = sprintf('Ошибка в файле: %s', $jsonError);
					//$output->writeln($msg);
					$this->logger->err($msg);
					//$this->get('session')->getFlashBag()->add('alert-danger', $msg);
					return false;
				}

				foreach ($json as $val) {
					if ( $val["Id"] == $neworder->getId() ){
						$msg = sprintf( 'Заявка на поставку уже была отправлена' );
						$this->logger->err($msg);
						//$output->writeln($msg);
						//$this->get('session')->getFlashBag()->add('alert-danger', $msg);
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

		$this->logger->info(
			sprintf('[SUPPKO.JSON] '.($action == "new" ? "New" : "Update").' order: %s', $neworder->getId())
		);

        $json_str = json_encode($json, JSON_UNESCAPED_UNICODE);
        $json_str = preg_replace('/\"Usersapid\":\"(\d+)\"/','"Usersapid":$1',$json_str);

    	file_put_contents($file, $json_str);
    	return;
    }

	/**
	 * Отравка email уведомлений о смене статуса заказа
	 *
	 * @return bool
	 */
	public function sendEmailChangeStatusOrder( $order, $type=null )
	{
		$container = $this->kernel->getContainer();
		$email = $order->getUser()->getEmail();
		$templating = $container->get('templating');

		$body = $templating->render('Top10CabinetBundle:Mail:ChangeStatusMail.html.twig', array(
			'order' => $order,
		));

		$subject = $type == 'supply' || $type == 'supplyok' ? 'Поставка заказа. Новый статус поставки – Кабинет Оптовика' : 'Новый статус заказа – Кабинет Оптовика';

		/* @var $message \Swift_Mime_Message */
		$message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setContentType("text/html")
			->setFrom($container->getParameter('top10_cabinet.emails.default'))
			->setTo($email)
			->setBody($body);
		 
		$container->get('mailer')->send($message);

		//Если ПОСТАВКА то отправляем еще на 2 адресса managersupply и getEmailmanagerDisk/Tire
		if ( $type == 'supply' ||  $type == 'supplyok' ){
			$message->setTo($container->getParameter('top10_cabinet.emails.managersupply'));
			$container->get('mailer')->send($message);

			if ( $order->getType() == 'disk' && $order->getUser()->getEmailmanagerDisk() != null && $order->getUser()->getEmailmanagerDisk() != '' ){
				$message->setTo( $order->getUser()->getEmailmanagerDisk() );
				$container->get('mailer')->send($message);
			}
			if ( $order->getType() == 'tire' && $order->getUser()->getEmailmanagerDisk() != null && $order->getUser()->getEmailmanagerDisk() != '' ){
				$message->setTo( $order->getUser()->getEmailmanagerDisk() );
				$container->get('mailer')->send($message);
			}

		}

		return true;
	}

    /**
     * Импортировать 3.json
     * Обертка для @see parse
     *
     * @return void
     */
	public function import()
	{
		$env = $this->kernel->getEnvironment();

		try {
			// parse json
			$result = $this->parse();
		}
		catch( JsonImportException $e ) {
			$this->handleException($e, $this->file);
			
			return;
		}

		$this->handleSuccess($result['messages'], $this->file);
		
		if ($result['change_status_order']){
		    foreach ($result['ch_st_orders'] as $order){
				if( $order->getSentmail() == 1 )
					$this->sendEmailChangeStatusOrder($order);
			}
		}

		// delete file
		if( $env === "prod" ) {
			unlink( $this->file );
		}

		return;
	}
}