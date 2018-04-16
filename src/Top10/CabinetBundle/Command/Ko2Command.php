<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\Cabinetorder;

class Ko2Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:ko2')
            ->setDescription('Новые заказы')
//             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//             ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	fopen("var/ko2", "w+");
    	$container 	= $this->getContainer();
    	$logger 	= $container->get('logger');
    	$logger->info("НАЧАЛО ВЫГРУЗКИ В 2.JSON");
    	$orders 	= $container->get("doctrine")->getRepository('Top10CabinetBundle:Cabinetorder')
	    						->findByNew(true);

    	if(is_array($orders)) {
    		$userjson = array();
    		foreach ($orders as $order){
    			$jsproducts = array();
    			$products = $order->getProductsorders();
    			foreach ($products as $product){
    				$jsproducts[] = array(
    								$product->getProduct()->getArticle(), 
    								$product->getQuantity(), 
//     								$product->getPrice()
    						);
    			}
	    		$userjson[] = array(
	    			"Id" 		=> $order->getId(),
	    			"Sapid"		=> $order->getSapid(),
	    			"Date" 		=> $order->getDate(),
// 		    		"Price" 	=> $order->getPrice(),
		    		"Usersapid" => str_pad($order->getUser()->getSapid(), 6, "0", STR_PAD_LEFT),
	    			"Message"	=> $order->getMessage(),
	    			"Type"		=> ($order->getType() == "disk" ? "41" : "14"),
	    			"Gpsb"		=> ($order->getType() == "disk" ? "80" : "70"),
		    		"Products" 	=> $jsproducts
	    		);
	    		$logger->info(
	    			sprintf('[2.JSON] New order: %s', $order->getId())
	    		);
	    	}
            $json_str = json_encode($userjson);
            $json_str = preg_replace('/\"Usersapid\":\"(\d+)\"/','"Usersapid":$1',$json_str);
	    	file_put_contents("var/ko/2.json", $json_str);
    	}
    	
    	$logger->info("КОНЕЦ ВЫГРУЗКИ В 2.JSON");
    	unlink("var/ko2");
    }
}
