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
use Top10\CabinetBundle\Service\JsonImport;
use Symfony\Bridge\Monolog\Logger;

class SapBalancesCommand extends ContainerAwareCommand
{

    private $file = "var/sap/balances.json";
    private $fileReport = "var/sap/balancesjson_report.txt";

    protected function configure()
    {
        $this
            ->setName('cabinet:sapbalances')
            ->setDescription('Обновление цен и количества товаров');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->file)) {
            $output->writeln('Файл не найден');
            return false;
        }

        $container = $this->getContainer();
        /**
         * @var $em EntityManager
         * @var $jsonImport JsonImport
         * @var $repository ProductRepository
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
        $noArticle = 0;
		$factory = null;
		$type = "";

        $logger->info("Start cabinet:sapbalances");

        $fileContent = file_get_contents($this->file);
        $json = $jsonImport->jsonValidate($fileContent, $jsonError);

        if ($jsonError) {
            $msg = sprintf('Ошибка в файле: %s', $jsonError);
            $output->writeln($msg);
            $logger->err($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }
        if (!is_array($json)) {
            $msg = "В balances.json неверные данные";
            $output->writeln($msg);
            $logger->info($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }

        $output->writeln(sprintf('Количество строк в файле: %d', count($json)));
        $logger->info("cabinet:sapbalances Всего в файле: " . count($json));

        if (file_exists($this->fileReport)) {
            unlink($this->fileReport);
        }
        file_put_contents($this->fileReport, "GPmater | Article", FILE_APPEND | LOCK_EX);

        
        foreach ($json as $key => $res) {
            $index = $key + 1;

            if (!isset($res->Article)) {
                $noArticle++;
                continue;
            }
            /** @var $product Product */
            $product = $repository->findOneByArticle($res->Article);
            if ($product) {
				$type = $product->getType();

				if( $type == 'tire' )
					$factory = '1401';//пока так потом можно в json файл добавить номер завода
				if( $type == 'disk' )
					$factory = '4101';

				$factoryObject = $em->getRepository('Top10CabinetBundle:Factory')->findOneBySapid($factory);
				$product = $repository->findOneBy(array( 'article' => $res->Article, 'factory' => $factoryObject->getId() ));

                try {
                    $updated++;
                    $product->setQuantity((int)$res->Quantity);
                    $product->setQuantityres((int)$res->Quantityres);
                    $product->setGpmater(trim($res->GPmater));
                    $product->setPrice01((float)$res->Price01);
                    $product->setPrice02((float)$res->Price02);
                    $product->setPrice03((float)$res->Price03);
                    $product->setPrice04((float)$res->Price04);
                    $product->setPrice05((float)$res->Price05);
                    $product->setJsonUpdate(1); // Успешно обновили товар
                } catch (\Exception $e) {
                    $output->writeln(sprintf('Caught exception: %s', $e->getMessage()));
                    $logger->err($e->getMessage());
                    continue;
                }
            } else {
                $notFounds[] = trim($res->GPmater) . " | " . $res->Article;
            }

            if ($index % 1000 == 0) {
                $output->writeln(sprintf('Обработано строк: %d', $index));
                $em->flush();
                $em->clear();
            }
        }
        $output->writeln(sprintf('Обработано строк: %d', $index));
        $em->flush();
        $em->clear();

        $output->writeln('Конец загрузки из balances.json');
        $output->writeln(sprintf('Товаров обновлено: %d', $updated));
        $output->writeln(sprintf('Товаров не найдено: %d', count($notFounds)));
        $output->writeln(sprintf('Товаров без артикула: %d', $noArticle));
        $logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ BALANCES.JSON; Товаров обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Товаров без артикула: " . $noArticle);
        $messages[] = "Всего в файле: " . count($json) . "; Товаров обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Товаров без артикула: " . $noArticle;

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