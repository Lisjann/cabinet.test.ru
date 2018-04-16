<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\Cabinetorder;

class Ko4Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:ko4')
            ->setDescription('Заказы на удаление')
//             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//             ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$container = $this->getContainer();
    	$logger = $container->get('logger');
    	$userjson = array();
    	$logger->info("НАЧАЛО ВЫГРУЗКИ В 4.JSON");
    	if(file_exists("var/ko2")) {
    		$logger->info("[4.JSON] Блокирован ko2 ВЫХОД");
    		return false;
    	}
    	$em = $container->get("doctrine")->getEntityManager();
    	$query = $em->createQuery(
    		'SELECT c FROM Top10CabinetBundle:Cabinetorder c WHERE c.todelete = :todelete AND c.sapid IS NOT NULL'
    	)->setParameter('todelete', '1');
    	$orders = $query->getResult();
    	if(is_array($orders)) {
    		foreach ($orders as $order){
	    		$userjson[] = $order->getSapid();
	    		$logger->info(
	    			sprintf('[4.JSON] Order to remove: %s', $order->getSapid())
	    		);
	    	}
	    	if(count($userjson)) file_put_contents("var/ko/4.json", json_encode($userjson));
    	}
    	$logger->info("КОНЕЦ ВЫГРУЗКИ В 4.JSON. Найдено ".count($userjson)." заказов на удаление");
    }
}
