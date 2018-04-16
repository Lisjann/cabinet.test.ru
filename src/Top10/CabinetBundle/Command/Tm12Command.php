<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Top10\CabinetBundle\Service\Json12Import;
use Top10\CabinetBundle\Service\CartManager;
use Top10\CabinetBundle\Controller;
//use Top10\CabinetBundle\Controller\DefaultControllerTest;
use Monolog\Logger;

class Tm12Command extends ContainerAwareCommand
{
	/**
	 * php app/console --env=dev cabinet:tm12
	 */
	protected function configure()
	{
		$this
			->setName('cabinet:tm12')
			->setDescription('Загрузка информации из ТМ для добавления заказа в КО');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();

		/** @var $logger Logger */
		$logger = $container->get('logger');
		/** @var $j12importer Json12Import */

		$j12importer = $container->get('cabinet.json12_import');


		$logger->info("НАЧАЛО АВТОРЕЗЕРВ ТОЧКА-МАРКЕТ");

		$parseResult = $j12importer->parse();
		$logger->info( $parseResult );

		//$orderResult = $j12importer->cartcheckoutAction( 'tire' );
		//$logger->info( $orderResult );

		$logger->info( "КОНЕЦ АВТОРЕЗЕРВ ТОЧКА-МАРКЕТ" );

		return 1;
	}

}