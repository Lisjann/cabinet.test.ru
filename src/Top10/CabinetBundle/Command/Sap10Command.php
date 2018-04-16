<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\Status;

class Sap10Command extends ContainerAwareCommand
{
	
	private $file = "var/sap/10.json";
	
    protected function configure()
    {
        $this
            ->setName('cabinet:sap10')
            ->setDescription('Добаление/обновление статусов заказов')
//             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//             ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$container = $this->getContainer();
    	$container->get('logger')->info("НАЧАЛО ЗАГРУЗКИ ИЗ 10.JSON");
    	$em = $container->get("doctrine")->getEntityManager();
    	
    	if(file_exists($this->file) && $data = json_decode(file_get_contents($this->file))){
    		
    		$repository = $container->get("doctrine")
    			->getRepository('Top10CabinetBundle:Status');
    		
    		if(is_array($data)){
	    		foreach ($data as $res){
	    			$status = false;
	    			if(!isset($res->Sapid)){
	    				$container->get('logger')->info("НЕТ ID СТАТУСА 10.JSON");
	    				continue;
	    			}
	    			$status = $repository->findOneBySapid($res->Sapid);
	    			if(!is_object($status)) $status = new Status();
	    			$status->setSapid($res->Sapid);
	    			$status->setName($res->Name);
	    			$status->setDescription($res->Description);
	    			$em->persist($status);
	    			$em->flush();
	    		}
	    		
	    		if($container->get('kernel')->getEnvironment() != "dev")
	    			unlink($this->file);
	    		
    		}else 
    			$container->get('logger')->info("В 10.JSON НЕВЕРНЫЕ ДАННЫЕ");
    	}
    	
    	$container->get('logger')->info("КОНЕЦ ЗАГРУЗКИ ИЗ 10.JSON");
        
    }
   
}
