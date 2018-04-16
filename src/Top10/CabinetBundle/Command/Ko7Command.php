<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\User;

class Ko7Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:ko7')
            ->setDescription('Новые заявки на высылку файлов клиентам')
//             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//             ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$container = $this->getContainer();
    	$logger = $container->get('logger');
    	$logger->info("НАЧАЛО ВЫГРУЗКИ В 7.JSON");
    	
    	$em = $container->get("doctrine")->getEntityManager();
    	$files = $container->get("doctrine")
	    	->getRepository('Top10CabinetBundle:File')
	    	->findAll();
    	
    	$daylife = $container->getParameter('top10_cabinet.day_live_order_files')*60*60*24;
    	
    	if(is_array($files)) {
    		$userjson = array();
    		foreach ($files as $file){
	    		$userjson[] = array(
	    			"Orderid" 	=> $file->getCabinetorder()->getSapid(),
	    			"Type" 		=> $file->getType()
	    		);
	    		$logger->info(
	    			sprintf('[7.JSON] New file add to send: %s', $file->getId())
	    		);
	    		
	    		if(time() - $file->getCreated()->getTimestamp() >= $daylife){
	    			$em->remove($file);
	    			$em->flush();
	    		}
	    	}
	    	file_put_contents("var/ko/7.json", json_encode($userjson));
	    	$logger->info("[7.JSON] Заявок: ".count($files)." шт");
    	}
    	
    	$logger->info("КОНЕЦ ВЫГРУЗКИ В 7.JSON");
    	
    }
}
