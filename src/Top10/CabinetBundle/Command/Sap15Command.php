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

class Sap15Command extends ContainerAwareCommand
{
    //private $file = "var/sap/15.json";
    private $file = "var/sap/import.txt";
    private $fileReport = "var/sap/import_report.txt";

    protected function configure()
    {
        $this
            ->setName('cabinet:insert:products')
            ->setDescription('Добавление и обнавление товаров');
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
        $articles  = array();
		$setInsert = true;
		$factory = '1401';

        $logger->info("Start cabinet:insert:products");

		$fileContent = fopen( $this->file, "r" );

        $json = $jsonImport->CSVValidate( $fileContent );

        if ($jsonError) {
            $msg = sprintf('Ошибка в файле: %s', $jsonError);
            $output->writeln($msg);
            $logger->err($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }
        if (!is_array($json)) {
            $msg = "В import.txt неверные данные";
            $output->writeln($msg);
            $logger->info($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }

        $output->writeln(sprintf('Количество строк в файле: %d', count($json)));
        $logger->info("cabinet:insert:products Всего в файле: " . count($json));

        if (file_exists($this->fileReport)) {
            unlink($this->fileReport);
        }
        file_put_contents($this->fileReport, "GPmater | Article", FILE_APPEND | LOCK_EX);

        $type = "";

//$output->writeln(print_r($json));

		
		foreach ($json as $key => $res) {
            $index = $key + 1;

			if (!isset($res->Article)) {
				$noArticle++;
				continue;
			}
            /** @var $product Product */
			$res->Article = ( ltrim( trim($res->Article), "0000000000" ) );

			if( $res->WidthTire > 0 ){
				$type = 'tire';
				$factory = '1401';//пока так потом можно в import.txt файл добавить номер завода
			}
			if( $res->WidthDisk > 0 ){
				$type = 'disk';
				$factory = '4101';
			}
			if( $res->WidthTire == '' && $res->WidthDisk == '' ){
				$type = 'fasteners';
				$factory = '4101';
			}

			

			//$product = $repository->findOneByArticle($res->Article);
			$factoryObject = $em->getRepository('Top10CabinetBundle:Factory')->findOneBySapid($factory);
			$product = $repository->findOneBy(array( 'article' => $res->Article, 'factory' => $factoryObject->getId() ));

			if( !$product ) {
				$product = new Product();
				$product->setArticle( $res->Article );
				$em->persist($product);
				$notFounds[] = trim($res->GPmater) . " | " . $res->Article;
				$setInsert = true;
			}
			/*if ($product) {*/
                //$articles[] = $res->Article;
				if ($type == "") {
                    $type = $product->getType();
					$repository->setJsonUpdates($type, $factory);
                }
                try {
					$updated++;
					$brand = trim($res->Brand);

					if($brand == 'K&amp;K')
						$brand = 'K&K';


					$price = $type == 'tire'? round($res->Price/1.15) : round($res->Price/1.20);

					if( $res->Quantity == 12 && $product->getQuantity() > 12 )
						$quantity = $product->getQuantity();
					else
						$quantity = $res->Quantity;


					//$quantityres = $quantityres > $quantity ? $res->Quantityres - $quantity : null;

					//$quantity = $product->getQuantity();
					$radius = $type == 'tire' ? $res->RadiusTire : $res->RadiusDisk;
					$width = $type == 'tire' ? $res->WidthTire : $res->WidthDisk;
					$width = round($width, 1);
					$width = $width == floor($width) ? floor($width) : $width;

					$Wheelbase = round($res->Wheelbase, 1);
					$Wheelbase = $Wheelbase == floor($Wheelbase) ? floor($Wheelbase) : $Wheelbase;

					$Centralhole = round($res->Centralhole, 1);
					$Centralhole = $Centralhole == floor($Centralhole) ? floor($Centralhole) : $Centralhole;

					$product->setName(trim($res->Name));
					if( $setInsert === true ) 
						$product->setType($type);

					$product->setArticleExternal( trim($res->ArticleExternal) );
					$product->setRadius( (int)trim($radius) );
					$product->setWidth( trim($width) );
					$product->setSeason( trim($res->Season) );
					$product->setNumberfixtures( trim($res->Numberfixtures) );
					$product->setWheelbase( $Wheelbase );
					$product->setBoom( (int)trim($res->Boom) );
					$product->setCentralhole( trim($Centralhole) );
					$product->setMaterial( trim($res->Material) );
					$product->setHeight( (int)trim($res->Height) );
					$product->setMaxload( trim($res->Maxload) );
					$product->setMaxspeed( (int)trim($res->Maxspeed) );
					$product->setCamera(trim($res->Camera));
					$product->setBrand($brand);
					$product->setQuantity( (int)$quantity );
					$product->setQuantityres( (int)$res->Quantityres );
					$product->setGpmater( trim( $res->GPmater ) );

					if( $setInsert === true ){
						$product->setPrice03((float)$price);
						$product->setPrice06((float)$res->Pricerecomend);
						$product->setPrice02((float)$res->PriceVIP);
					}

					$product->setFactory($factoryObject);

					$product->setJsonUpdate(1); // Успешно обновили товар

                } catch (\Exception $e) {
                    $output->writeln(sprintf('Caught exception: %s', $e->getMessage()));
                    $logger->err($e->getMessage());
                    continue;
                }
            /*} else {
                $notFounds[] = trim($res->GPmater) . " | " . $res->Article;
            }*/

            if ($index % 1000 == 0) {
                $output->writeln(sprintf('Обработано строк: %d', $index));
                $em->flush();
                $em->clear();
            }
        }
        $output->writeln(sprintf('Обработано строк: %d', $index));
        $em->flush();
        $em->clear();

        /**
         * Если в выгружаемом файле import.txt товар отсутствует (т.е остатки по нему нулевые и он не попал в файл),
         * а в КО ранее данный материал был, то по товару должны обнулиться наличие и все цены.
         */

        $output->writeln('Обнуляем остатки и цены у товаров не попавших в import.txt');
		$repository->updateProductPrices($type, $factoryObject->getId(), true);

        $output->writeln('Конец загрузки из import.txt');
        $output->writeln(sprintf('Товаров не найдено и добавленно: %d', count($notFounds)));
        $output->writeln(sprintf('Товаров обновлено: %d', $updated));
        $output->writeln(sprintf('Товаров без артикула: %d', $noArticle));
        $logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ IMPORT.TXT; Товаров обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Товаров без артикула: " . $noArticle);
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