<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\User;
use Top10\CabinetBundle\Entity\Payment;
use Top10\CabinetBundle\Service\JsonImport;

class Sap8Command extends ContainerAwareCommand
{
	
	private $file = "var/sap/8.json";
	
    protected function configure()
    {
        $this
            ->setName('cabinet:sap8')
            ->setDescription('Загрузка платежей по клиентам')
//             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//             ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
    	
    	if(!file_exists($this->file)) {
    		echo "Файл не найден\r\n";
    		return false;
    	}
    	
    	$container 	= $this->getContainer();
    	$logger 	= $container->get('logger');
    	$logger->info("НАЧАЛО ЗАГРУЗКИ ИЗ 8.JSON");
    	
    	if(file_exists("var/sap5")) {
    		$logger->info("[8.JSON] Блокирован sap5 ВЫХОД");
    		return false;
    	}
    	
    	$user_no_id = false;
    	$env 		= $container->get('kernel')->getEnvironment();
    	$err_catch 	= array();
    	/** @var $jsonImport JsonImport */
    	$jsonImport = $container->get('cabinet.json_import');
    	
    	$fileContent = file_get_contents($this->file);
    	$jsonError 	= false;
    	$json 		= $jsonImport->jsonValidate($fileContent, $jsonError);
    	
    	if($jsonError) {
    		echo $jsonError."\r\n";
    		$logger->err("File 8.json corrapted. Error: ".$jsonError);
    		$err_catch[]="File 8.json corrapted. Error: ".$jsonError;
    		$jsonImport->sendEmail($err_catch,$this->file);
    		return false;
    	}
    	
    	$em 		  = $container->get("doctrine")->getEntityManager();
    	//$repository = $em->getRepository('Top10CabinetBundle:Payment');
    	$repUser 	  = $em->getRepository('Top10CabinetBundle:User');
    		
    	if(!is_array($json)) return false;

		$qb = $em->createQueryBuilder();
		$q = $qb->delete('Top10CabinetBundle:Payment')->getQuery();
		$q->execute();

	    foreach ($json as $res){
	    	if(!isset($res->Userid)){
	    		$logger->info("НЕТ USER ID ТОВАРА 8.JSON");
	    		$err_catch[]="НЕТ USER ID ТОВАРА 8.JSON";
	    		continue;
	    	}

	    	$user = $repUser->findOneBySapid($res->Userid+0);
	    	if(is_object($user)){
				$payment = new Payment();
				$payment->setUser($user);
				$payment->setType(trim($res->Type));
    			$payment->setData(new \DateTime("@".trim($res->Data)));
    			$payment->setNumberdoc(trim($res->Numberdoc)+0);
    			$payment->setDescription(trim($res->Description));
    			$payment->setPrice(trim($res->Price));
    			$payment->setDelay(trim($res->Delay));
    			//$payment->setDebt(trim($res->Debt));
    			$payment->setOverdue(trim($res->Overdue));
    			$payment->setDuty(trim($res->Duty));
    			$payment->setFines(trim($res->Fines));
    			$em->persist($payment);
    			$em->flush();
	    	}else $user_no_id = true;
	    }
	    		
    	if($user_no_id) {
    		$logger->info("В 8.JSON ЕСТЬ ПЛАТЕЖИ С НЕВЕРНЫМ ID USER");
    		$err_catch[]="В 8.JSON ЕСТЬ ПЛАТЕЖИ С НЕВЕРНЫМ ID USER";
    	}
	    		
	    if($env != "dev") unlink($this->file);
    		
    	$jsonImport->sendEmail($err_catch,$this->file);
    	
    	$logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ 8.JSON");
        
    }
   
}