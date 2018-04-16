<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\Model;
use Top10\CabinetBundle\Entity\File;
use Doctrine\ORM\EntityManager;
use Top10\CabinetBundle\Service\JsonImport;
use Symfony\Bridge\Monolog\Logger;

class ModelCommand extends ContainerAwareCommand
{
    private $file = "var/ko/model.json";
    private $fileReport = "var/sap/model_report.txt";

    protected function configure()
    {
        $this
            ->setName('cabinet:insert:model')
            ->setDescription('Добавление и обнавление Моделей');
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
         * @var $repository modelRepository
         */
        $em = $container->get("doctrine")->getManager();
        $jsonImport = $container->get('cabinet.json_import');
        $repository = $em->getRepository('Top10CabinetBundle:Model');
        $logger = $container->get('logger');
        $env = $container->get('kernel')->getEnvironment();

        $messages  = array();
        $notFounds = array();
        $jsonError = false;
        $attach    = false;
        $updated   = 0;
        $noTmid = 0;
        $Tmids  = array();

        $logger->info("Start cabinet:insert:model");

        $fileContent = file_get_contents($this->file);
		//$fileContent = fopen( $this->file, "r" );

        $json = $jsonImport->jsonValidate($fileContent, $jsonError);
        //$json = $jsonImport->CSVValidate( $fileContent );

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
        $logger->info("cabinet:insert:model Всего в файле: " . count($json));

        if (file_exists($this->fileReport)) {
            unlink($this->fileReport);
        }
        file_put_contents($this->fileReport, "NAME | Tmid", FILE_APPEND | LOCK_EX);

        $type = "";

		foreach ($json as $key => $res) {
            $index = $key + 1;

			if (!isset($res->ID)) {
				$noTmid++;
				continue;
			}
            /** @var $model model */
            $model = $repository->findOneByTmid($res->ID);
			if( !$model ) {
				$model = new model();
				$model->setTmid( $res->ID );
				$em->persist($model);
				$notFounds[] = trim($res->NAME) . " | " . $res->ID;
			}
                $Tmid[] = $res->ID;
                if ($type == "") {
                    $type = $model->getType();
                }
                try {

					$updated++;

					$model->setName(trim($res->NAME));
					$model->setType($res->TYPE);
					$repFile = $em->getRepository('Top10CabinetBundle:File');


					foreach($res->PICTURE as $picture){
//print_r( $picture );
						$flle = $repFile->findOneBy( array( 'model' => $model->getId(), 'type' => $picture->COLOR ) );
						if( !$flle ) {
							$flle = new File();
							$flle->setModel( $model );
							$em->persist($flle);
						}

						$flle->setUrl( '/img/' . $picture->URL );
						$flle->setType( $picture->COLOR );
						$em->persist($flle);

					}

                } catch (\Exception $e) {
                    $output->writeln(sprintf('Caught exception: %s', $e->getMessage()));
                    $logger->err($e->getMessage());
                    continue;
                }


            if ($index % 1000 == 0) {
                $output->writeln(sprintf('Обработано строк: %d', $index));
                $em->flush();
                $em->clear();
            }
        }

        $output->writeln( sprintf('Обработано строк: %d', $index) );
        $em->flush();
        $em->clear();

        $output->writeln('Конец загрузки из model.json');
        $output->writeln(sprintf('Моделей не найдено и добавленно: %d', count($notFounds)));
        $output->writeln(sprintf('Моделей обновлено: %d', $updated));
        $output->writeln(sprintf('Моделей без артикула: %d', $noTmid));
        $logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ MODEL.JSON; Моделей обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Моделей без TMID: " . $noTmid);
        $messages[] = "Всего в файле: " . count($json) . "; Моделей обновлено: " . $updated . '; Не найдено: ' . count($notFounds) . "; Моделей без TMID: " . $noTmid;

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