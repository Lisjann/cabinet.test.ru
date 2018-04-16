<?php

namespace Top10\CabinetBundle\Service;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Top10\CabinetBundle\Classes\JsonImportException;
use Doctrine\ORM\EntityManager;
use Top10\CabinetBundle\Entity\Product;
use Top10\CabinetBundle\Entity\ProductsOrders;
use Monolog\Logger;
use Top10\CabinetBundle\Service\JsonImport;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Console\Output\OutputInterface;

class Json6Import extends JsonImport
{
	protected $em;

	public function __construct(EntityManager $em, Logger $logger, Kernel $kernel)
	{
		parent::__construct( $kernel, $logger);
		$this->em = $em;
	}

	public function import(OutputInterface $output, $file, $fileReport, $factory)
	{
		if(!file_exists($file)) {
			throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_FOUND);
		}

		$container = $this->kernel->getContainer();
		/**
		 * @var $em EntityManager
		 * @var $jsonImport JsonImport
		 * @var $repository ProductRepository
		 */
		$em = $this->em;

		$repository = $em->getRepository('Top10CabinetBundle:Product');

		$factoryObject= $em->getRepository('Top10CabinetBundle:Factory')->findOneBySapid($factory);

		$logger = $container->get('logger');
		$env = $container->get('kernel')->getEnvironment();

		$messages		  = array();
		$notFounds		  = array();
		$notFoundsFactory = array();
		$jsonError 		  = false;
		$attach			  = false;
		$updated		  = 0;
		$noArticle		  = 0;
		$index			  = 0;
		$articles		  = array();

		$logger->info("Start cabinet:sap6");

		$fileContent = file_get_contents($file);
		$json = $this->jsonValidate($fileContent, $jsonError);

		if ($jsonError) {
			$msg = sprintf('Error in file: %s', $jsonError);
			$output->writeln($msg);
			//echo $msg;
			$logger->err($msg);
			$messages[] = $msg;
			$this->sendEmail($messages, $file);
			return false;
		}
		if (!is_array($json)) {
			$msg = "In " . $factory . " wrong data";
			$output->writeln($msg);
			$logger->info($msg);
			$messages[] = $msg;
			$this->sendEmail($messages, $file);
			return false;
		}
		if( count($json) == 0 )
			$repository->setJsonUpdates( null, $factoryObject->getId() );

        $output->writeln(sprintf('Count line in file: %d', count($json)));
		//echo sprintf('Count line in file: %d', count($json));
        $logger->info("cabinet:sap6 Всего в файле: " . count($json));

        if (file_exists($fileReport)) {
            unlink($fileReport);
        }
        file_put_contents($fileReport, "GPmater | Article", FILE_APPEND | LOCK_EX);

        $type = "";
        foreach ($json as $key => $res) {
            $index = $key + 1;

            if (!isset($res->Article)) {
                $noArticle++;
                continue;
            }
            /** @var $product Product */
            $product = $repository->findOneBy(array( 'article' => $res->Article, 'factory' => $factoryObject->getId() ));

			if( !$product ){

				$product = $repository->findOneByArticle($res->Article);
				if( $product ){
					$productNew = new Product();
					$productNew->setArticle( $product->getArticle() );
					$productNew->setArticleExternal( $product->getArticleExternal() );
					$productNew->setName( $product->getName() );
					$productNew->setImage( $product->getImage() );
					$productNew->setType( $product->getType() );
					$productNew->setRadius( $product->getRadius() );
					$productNew->setWidth( $product->getWidth() );
					$productNew->setSeason( $product->getSeason() );
					$productNew->setNumberfixtures( $product->getNumberfixtures() );
					$productNew->setWheelbase( $product->getWheelbase() );
					$productNew->setBoom( $product->getBoom() );
					$productNew->setCentralhole( $product->getCentralhole() );
					$productNew->setMaterial( $product->getMaterial() );
					$productNew->setHeight( $product->getHeight() );
					$productNew->setMaxload( $product->getMaxload() );
					$productNew->setMaxspeed( $product->getMaxspeed() );
					$productNew->setCamera($product->getCamera() );
					$productNew->setBrand($product->getBrand() );
					//$productNew->setQuantity( $product->getQuantity() );
					//$productNew->setQuantityres( $product->getQuantityres() );
					$productNew->setGpmater( $product->getGpmater() );
					$productNew->setModel( $product->getModel() );

					$productNew->setFactory($factoryObject);

					$em->persist($productNew);
					$notFoundsFactory[] = trim($res->GPmater) . " | " . $res->Article;

					$product = $productNew;
				}
			}

			if( $product ){
                $articles[] = $res->Article;
                if ($type == "") {
                    $type = $product->getType();
                    $repository->setJsonUpdates( $type, $factoryObject->getId() );
                }
                try {
                    $updated++;
                    $product->setQuantity((int)$res->Quantity);
                    $product->setGpmater(trim($res->GPmater));
                    $product->setPrice01((float)$res->Price01);
                    $product->setPrice02((float)$res->Price02);
                    $product->setPrice03((float)$res->Price03);
                    $product->setPrice04((float)$res->Price04);
                    $product->setPrice05((float)$res->Price05);
                    $product->setPrice05((float)$res->Price05);
                    $product->setPrice06((float)$res->Price06);
                    $product->setPrice09((float)$res->Price09);
                    //$product->setFactory($factoryObject);
					
                    $product->setJsonUpdate(1); // Успешно обновили товар
                } catch (\Exception $e) {
                    $output->writeln(sprintf('Caught exception: %s', $e->getMessage()));
                    //echo sprintf('Caught exception: %s', $e->getMessage());

                    $logger->err($e->getMessage());
                    continue;
                }
            } else {
                $notFounds[] = trim($res->GPmater) . " | " . $res->Article;
            }
            if ($index % 1000 == 0) {

                $output->writeln(sprintf('Processed Rows: %d', $index));

                $em->flush();
                $em->clear();
            }
        }
        $output->writeln(sprintf('Processed Rows: %d', $index));
        //echo sprintf('Обработано строк: %d', $index);
        $em->flush();
        $em->clear();

        /**
         * Если в выгружаемом файле 6.json товар отсутствует (т.е остатки по нему нулевые и он не попал в файл),
         * а в КО ранее данный материал был, то по товару должны обнулиться наличие и все цены.
         */

        $output->writeln('We reset the balances and prices of goods not included in the '. $factory .'.json');
        //echo 'Обнуляем остатки и цены у товаров не попавших в 6.json';
        $repository->updateProductPrices($type, $factoryObject->getId());

        $output->writeln('End load in '. $factory .'.json');
        $output->writeln(sprintf('Product update: %d', $updated));
        $output->writeln(sprintf('Product not found: %d', count($notFounds)));
        $output->writeln(sprintf('Product not found is factory: %d', count($notFoundsFactory)));
        $output->writeln(sprintf('Product not article: %d', $noArticle));

        $logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ ". $factory .".JSON; Product update: " . $updated . '; Not found: ' . count($notFounds) . "; Product not article: " . $noArticle);
        $messages[] = "Всего в файле: " . count($json) . "; Product update: " . $updated . '; Not found: ' . count($notFounds) . "; Product not article: " . $noArticle;

        if (count($notFounds)) {
            $attach = $fileReport;
        }

        file_put_contents($fileReport, "\n" . implode("\n", $notFounds), FILE_APPEND | LOCK_EX);
        $this->sendEmail($messages, $file, $attach);

        if ($env != "dev") {
            unlink($file);
        }

		return true;
	}
}