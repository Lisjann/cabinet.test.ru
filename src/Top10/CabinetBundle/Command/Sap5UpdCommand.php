<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Service\Json5ImportUpd;
use Symfony\Bridge\Monolog\Logger;

/**
 * php app/console cabinet:sap5upd --env=dev
 *
 * Class Sap5UpdCommand
 * @package Top10\CabinetBundle\Command
 */
class Sap5UpdCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:sap5upd')
            ->setDescription('Активация партнеров, прошедших регистрацию')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('cabinet:sap5upd begin.');

        $container = $this->getContainer();

        /** @var $logger Logger */
        $logger = $container->get('logger');
        /** @var $j5importer Json5Import */
        $j5importer = $container->get('cabinet.json5upd_import');

        $output->writeln('Look for '.$j5importer->getFilePath());

        if( !file_exists( $j5importer->getFilePath() ) ) {
            $output->writeln($j5importer->getFilePath(). ' not search');
            return 1;
        }

        $output->writeln($j5importer->getFilePath(). ' search');

        $logger->info("Начало загрузки 5.json");

        $result = $j5importer->import();

		$output->writeln( 'count update users: ' . $result['countusers'] );
		$output->writeln( 'count NOT update users: ' . $result['countnotusers'] );

        $logger->info("Конец загрузки 5.json");

        return 1;
    }

}