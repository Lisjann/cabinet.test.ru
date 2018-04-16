<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\Model;
use Top10\CabinetBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Top10\CabinetBundle\Service\JsonImport;
use Symfony\Bridge\Monolog\Logger;

class ProductModelCommand extends ContainerAwareCommand
{
    //private $file = "var/sap/15.json";
    private $file = "var/ko/product.json";
    private $fileReport = "var/sap/model_report.txt";

    protected function configure()
    {
        $this
            ->setName('cabinet:insert:productmodel')
            ->setDescription('Add and update Product');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->file)) {
            $output->writeln('File not empty');
            return false;
        }

        $container = $this->getContainer();
        /**
         * @var $em EntityManager
         * @var $jsonImport JsonImport
         * @var $repository modelRepository
         */
        $em = $container->get("doctrine")->getManager();
        $jsonImport = $container->get('cabinet.json_import');
        $repository = $em->getRepository('Top10CabinetBundle:Product');
        $logger = $container->get('logger');
        $env = $container->get('kernel')->getEnvironment();

        $messages  = array();
        $notFounds = array();
        $jsonError = false;
        $attach    = false;
        $updated   = 0;
        $noTmid = 0;
        $Tmids  = array();

        $logger->info("Start cabinet:insert:productmodel");

        $fileContent = file_get_contents($this->file);
		//$fileContent = fopen( $this->file, "r" );

        $json = $jsonImport->jsonValidate($fileContent, $jsonError);
        //$json = $jsonImport->CSVValidate( $fileContent );

        if ($jsonError) {
            $msg = sprintf('Error in file: %s', $jsonError);
            $output->writeln($msg);
            $logger->err($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }
        if (!is_array($json)) {
            $msg = "В product.json неверные данные";
            $output->writeln($msg);
            $logger->info($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }

        $output->writeln(sprintf('quant string in file: %d', count($json)));
        $logger->info("cabinet:insert:productmodel quant in file: " . count($json));

        if (file_exists($this->fileReport)) {
            unlink($this->fileReport);
        }
        file_put_contents($this->fileReport, "NAME | Tmid", FILE_APPEND | LOCK_EX);

        $type = "";

		foreach ($json as $key => $res) {
            $index = $key + 1;

			if (!isset($res->ARTICLE)) {
				$noTmid++;
				continue;
			}
            /** @var $product product */
            $product = $repository->findOneByArticle($res->ARTICLE);
			if( $product ) {
					$updated++;

					$product->setColor(trim($res->COLOR));

					$repModel = $em->getRepository('Top10CabinetBundle:Model');

					$model = $repModel->findOneByTmid($res->MODELID);

					$product->setModel( $model );

				if ($index % 1000 == 0) {
					$output->writeln(sprintf('processed strings: %d', $index));
					$em->flush();
					$em->clear();
				}
			}
		}

        $output->writeln( sprintf('Processed sting: %d', $index) );
        $em->flush();
        $em->clear();

        $output->writeln('End load in product.json');
        $output->writeln(sprintf('Product not empty and add: %d', count($notFounds)));
        $output->writeln(sprintf('Product updated: %d', $updated));
        $output->writeln(sprintf('Product not Article: %d', $noTmid));
        $logger->info("END LOAD IN PRODUCT.JSON; Product updated: " . $updated . '; Not empty: ' . count($notFounds) . "; Product not ARTIVLE: " . $noTmid);
        $messages[] = "q in file: " . count($json) . "; Продуктов обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Продуктов без TMID: " . $noTmid;

        if (count($notFounds)) {
            $attach = $this->fileReport;
        }

        file_put_contents($this->fileReport, "\n" . implode("\n", $notFounds), FILE_APPEND | LOCK_EX);
        $jsonImport->sendEmail($messages, $this->file, $attach);

        if ($env != "dev") {
            unlink($this->file);
        }
    }


}