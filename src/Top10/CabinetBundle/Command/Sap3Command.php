<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Service\Json3Import;
use Monolog\Logger;

class Sap3Command extends ContainerAwareCommand
{
    /**
     * php app/console --env=dev cabinet:sap3
     */
    protected function configure()
    {
        $this
            ->setName('cabinet:sap3')
            ->setDescription('Загрузка информации по заказам с SAP');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var $logger Logger */
        $logger = $container->get('logger');
        /** @var $j3importer Json3Import */
        $j3importer = $container->get('cabinet.json3_import');
        if( !file_exists( $j3importer->getFile() ) ) {
            return 1;
        }

        $logger->info("НАЧАЛО ЗАГРУЗКИ ИЗ 3.JSON");

        $j3importer->import();

        $logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ 3.JSON");

        return 1;
    }

}