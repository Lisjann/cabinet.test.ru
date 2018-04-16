<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Service\ProductImport;

/**
 * php app/console cabinet:import:products --env=dev
 *
 * Class Sap5Command
 * @package Top10\CabinetBundle\Command
 */
class ImportProductsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:import:products')
            ->setDescription('Обновление товаров из базы скрутиколес')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('cabinet:import:products begin.');

        $container = $this->getContainer();

        /** @var $pi ProductImport */
        $pi = $container->get('cabinet.product_import');
        $pi->import($output);

        $output->writeln("Конец обновления");

        return 1;
    }

}