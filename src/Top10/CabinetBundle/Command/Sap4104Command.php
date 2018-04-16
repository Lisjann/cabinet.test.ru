<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\Product;
use Top10\CabinetBundle\Entity\ProductRepository;
use Doctrine\ORM\EntityManager;
use Top10\CabinetBundle\Service\Json6Import;
use Symfony\Bridge\Monolog\Logger;

class Sap4104Command extends ContainerAwareCommand
{

	private $file = "var/sap/4104.json";

    protected function configure()
    {
        $this
            ->setName('cabinet:sap4104')
            ->setDescription('Обновление цен и количества товаров');
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$fileName = basename( $this->file );
		$factory = trim( basename( $this->file, 'json' ), '.' );
		$fileReport = "var/sap/" . $factory . "_report.txt";

		$container = $this->getContainer();

		/** @var $logger Logger */
		$logger = $container->get('logger');
		/** @var $j6importer Json6Import */
		$j6importer = $container->get('cabinet.json6_import');

		/*if( !file_exists( $this->file() ) ) {
			return 1;
		}*/

		$logger->info("НАЧАЛО ЗАГРУЗКИ ИЗ " . strtoupper($fileName) );

		$j6importer->import($output, $this->file, $fileReport, $factory);

		$logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ " . strtoupper($fileName) );

		return 1;
	}

}