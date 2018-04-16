<?php

namespace Top10\CabinetBundle\Tests\Service;

use Top10\CabinetBundle\Classes\JsonImportException;
use Top10\CabinetBundle\Service\Json3Import;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Top10\CabinetBundle\EventListener\EmailListener;

/**
 * phpunit -c app src/Top10/CabinetBundle/Tests/Service/Json3Test.php
 */
class Json3Test extends WebTestCase
{
    
    protected $importer;
    
    // разовый вызов функции перед всеми тестами один раз
    public static function setUpBeforeClass()
    {
        // если существуют блокирующие файлы, то удаляем их
        $files = array('var/test/sap5','var/test/sap11');
        foreach ($files as $file){
            if(file_exists($file)) {
                unlink( $file );
            }
        }
    }
    
    // вызов перед каждым тестом
    protected function setUp()
    {
        $client = static::createClient();
        $container = $client->getKernel()->getContainer();
        /** @var $importer Json3Import */
        $importer = $container->get('cabinet.json3_import');
        $importer->setJsondir("var/test/");
        $this->importer = $importer;
    }
    
    // проверка на блокировку sap5
    public function testSap5Blocked()
    {
        $file = $this->importer->getJsondir()."sap5.json";
        if(!file_exists($file)) {
            fopen($file, "w+");
        }
        $importer = $this->importer;
        $importer->setFile($importer->getJsondir()."sap/3_validfile.json");
        
        try {
            $result = $importer->parse();
        }
        catch (JsonImportException $e) {
            $this->assertEquals($e->getCode(),JsonImportException::ERROR_JSON_BLOCKED_SAP5);
        }
        unlink( $file );
    }
    
    // проверка на блокировку sap11
    public function testSap11Blocked()
    {
        $file = $this->importer->getJsondir()."sap11";
        if(!file_exists($file)) {
            fopen($file, "w+");
        }
        $importer = $this->importer;
        $importer->setFile($importer->getJsondir()."sap/3_validfile.json");
    
        try {
            $result = $importer->parse();
        }
        catch (JsonImportException $e) {
            $this->assertEquals($e->getCode(),JsonImportException::ERROR_JSON_BLOCKED_SAP11);
        }
        unlink( $file );
    }
    
    public function testValidFile()
    {
    	$importer = $this->importer;
        $importer->setFile('var/test/sap/3_validfile.json');

    	// Валидный нормальный файл
        $result = array();
    	try {
    		$result = $importer->parse();
    	}
    	catch (JsonImportException $e) {
    		$this->fail($e->getMessage());
    	}
    	
    	$this->assertTrue( $result['result'] );
    }
    
    public function testCP1251File()
    {
    	// Не валидный файл (кодировка 1251)
    	$importer = $this->importer;
        $importer->setFile('var/test/sap/3_cp1251.json');
    	 
    	try {
    		$result = $importer->parse();
    	}
    	catch (JsonImportException $e) {
    		return;
    	}
    	$this->fail('Test failed');
    }
    
    public function testBadField()
    {
    	// Кривое поле id: вместо Id -> Ids
    	$importer = $this->importer;
        $importer->setFile('var/test/sap/3_badfield.json');

        $result = array();
    	try {
    		$result = $importer->parse();
    	}
    	catch (JsonImportException $e) {
    		$this->fail($e->getMessage());
    	}
    	$this->assertEquals($result['all'],1);
    	$this->assertEquals($result['updated'],0);
    	$this->assertEquals($result['no_found_tovar_by_id'],0);
    	$this->assertEquals($result['no_id_tovar'],1);
    }
    public function testBadField2()
    {
    	// Кривое поле id: 0
    	$importer = $this->importer;
        $importer->setFile('var/test/sap/3_badfield2.json');

        $result = array();
    	try {
    		$result = $importer->parse();
    	}
    	catch (JsonImportException $e) {
    		$this->fail($e->getMessage());
    	}
    	$this->assertEquals($result['all'],1);
    	$this->assertEquals($result['updated'],0);
    	$this->assertEquals($result['no_found_tovar_by_id'],1);
    	$this->assertEquals($result['no_id_tovar'],0);
    }
    
    public function testChangeStatusOrder()
    {

        $client = static::createClient();
        $container = $client->getKernel()->getContainer();
        $em = $container->get("doctrine")->getEntityManager();
        
        $plugin = new EmailListener();        
        $mailer = $container->get('mailer');
        $mailer->registerPlugin($plugin);
        
        $repStatus = $em->getRepository('Top10CabinetBundle:Status');
        $repUser = $em->getRepository('Top10CabinetBundle:User');
        
        $status = $repStatus->find(1);
//         $user = $repUser->find(1);
        $user = new \Top10\CabinetBundle\Entity\User();
        $user->setUsername("testuserforchangestatusorder");
        $user->setPassword("test");
        $user->setEmail("testuserforchangestatusorder@test.com");
        
        $order = new \Top10\CabinetBundle\Entity\Cabinetorder();
        
        $order->setStatus($status);
        $order->setUser($user);
        
        $em->persist($user);
        $em->persist($order); // сохраняем тестовый заказ
        $em->flush();
        $order_id = $order->getId();
        
        // меняем статус заказа с 1 на 2
        $arr = array(array('Id' => $order_id,
                     'Sapid' => '1',
                     'Price' => '1',
                     'Status' => '2',
                     'Products' => '0')); 
        $arr_json = json_encode($arr);
        
        $file = $this->importer->getJsondir()."sap/3_changestatus.json";
        $fp = fopen($file, "w");
        fwrite($fp, $arr_json);
        fclose ($fp);
        
        // запускаем импорт
        $importer = $container->get('cabinet.json3_import');
        $importer->setFile($file);
        
        $result = array();
        try {
            $result = $importer->import();
        }
        catch (JsonImportException $e) {
            $this->fail($e->getMessage());
        }
        $em->remove($user);  // удаляем тестового пользователя
        $em->remove($order); // удаляем из базы тестовый заказ
        $em->flush();

        $success = false;
        // ищем в отправленных наш email
        foreach ($plugin->getMessages() as $message) {
            if ( array_key_exists($user->getEmail(),$message->getTo())&&
                 ($message->getSubject()=='Новый статус заказа – Кабинет Оптовика')) 
            {
                $success = true;
            }
        }

        $this->assertTrue( $success, 'Email no sended' );
        unlink($file);
    }
}
