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
use Symfony\Bridge\Monolog\Logger;


class renameImgProductsCommand extends ContainerAwareCommand
{


    private $fileReport = "var/sap/renameimgprd_report.txt";

    protected function configure()
    {
        $this
            ->setName('cabinet:renameimgprd')
            ->setDescription('переименование картинки продукта');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

		$container = $this->getContainer();
        /**
         * @var $em EntityManager
         * @var $repository ProductRepository
         */
        $em = $container->get("doctrine")->getManager();
        $repository = $em->getRepository('Top10CabinetBundle:Product');

		$logger = $container->get('logger');
        $env = $container->get('kernel')->getEnvironment();


		$dir = 'web/img/';
		//$dir = 'www/img/';

		$messages  = array();
        $notFounds = array();
        $dirError = false;
        $attach    = false;
        $updated   = 0;
        $noArticle = 0;
        $articles  = array();

        $logger->info("Start cabinet:renameimgprd");

		$arrImgs = scandir($dir);

		$arrImgs = array_slice ($arrImgs, 2);

		if (!$arrImgs) {
            $output->writeln('Файл не найден');
            return false;
        }

        if ($dirError) {
            $msg = sprintf('Ошибка в файле: %s', $dirError);
            $output->writeln($msg);
            $logger->err($msg);
            $messages[] = $msg;
            return false;
        }
        if (!is_array($arrImgs)) {
            $msg = "В папке " . $dir . " неверные данные";
            $output->writeln($msg);
            $logger->info($msg);
            $messages[] = $msg;
            return false;
        }

        $output->writeln(sprintf('Кол. фалов в папке: %d', count($arrImgs)));
        $logger->info("cabinet:renameimgprd Кол. фалов в папке: " . count($arrImgs));

        if (file_exists($this->fileReport)) {
            unlink($this->fileReport);
        }

        file_put_contents($this->fileReport, "GPmater | Article", FILE_APPEND | LOCK_EX);

        $type = "";
        

		foreach ($arrImgs as $key => $res) {
			$index = $key + 1;

			if (!isset($res)) {
				$noArticle++;
				continue;
			}

			$ext = pathinfo( $dir . $res, PATHINFO_EXTENSION );

			$article =  strtoupper( str_replace( '.' . $ext, '', $res ) );

//$article = mb_convert_encoding($article, "UTF-8", "ASCII ");
$output->writeln( $article );

            /** @var $product Product */
            $product = $repository->findOneByArticle($article);
            if ($product) {
                $articles[] = $article;
                $updated++;
                $product->setImage(trim($res));
            } else
                $notFounds[] = $res;

            if ($index % 1000 == 0) {
                $output->writeln(sprintf('Обработано строк: %d', $index));
                $em->flush();
                $em->clear();
            }
        }

        $output->writeln( sprintf('Обработано строк: %d', $index) );
        $em->flush();
        $em->clear();

        /**
         * Если в выгружаемом файле 6.json товар отсутствует (т.е остатки по нему нулевые и он не попал в файл),
         * а в КО ранее данный материал был, то по товару должны обнулиться наличие и все цены.
         */

        $output->writeln('Конец загрузки из папки ' . $dir );
        $output->writeln(sprintf('Картинок у товаров обновлено: %d', $updated));
        $output->writeln(sprintf('Картинок у товаров не найдено: %d', count($notFounds)));
        $output->writeln(sprintf('Картинок у товаров без артикула: %d', $noArticle));
        $logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ из папки ". $dir . " Картинок обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Картинок без артикула: " . $noArticle);
        $messages[] = "Всего в папке: " . count($arrImgs) . "; Картинок обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Картинок без артикула: " . $noArticle;

        if (count($notFounds)) {
            $attach = $this->fileReport;
        }

        file_put_contents($this->fileReport, "\n" . implode("\n", $notFounds), FILE_APPEND | LOCK_EX);
        //$jsonImport->sendEmail($messages, $this->file, $attach);
        
    }


}