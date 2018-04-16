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

class SapAutoSupplyCommand extends ContainerAwareCommand
{

    private $file = "var/sap/suppsap.json";

    protected function configure()
    {
        $this
            ->setName('cabinet:sapAutoSupply')
            ->setDescription('Загрузка информации по поставкам с SAP');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->file)) {
            $output->writeln('file not found');
            //$output->writeln('Файл не найден');
            return false;
        }

        $container = $this->getContainer();
        /**
         * @var $em EntityManager
         * @var $jsonImport JsonImport
         * @var $repository CabinetorderRepository
         */
        $em				 = $container->get("doctrine")->getManager();
        $jsonImport		 = $container->get('cabinet.json_import');
        $repositorySupply = $em->getRepository('Top10CabinetBundle:Supply');
		$repository		 = $em->getRepository('Top10CabinetBundle:Cabinetorder');
		$repStatussupply = $em->getRepository('Top10CabinetBundle:Statussupply');
		$repStatus		 = $em->getRepository('Top10CabinetBundle:Status');
        $logger			 = $container->get('logger');
        $env			 = $container->get('kernel')->getEnvironment();
		$j3importer 	 = $container->get('cabinet.json3_import');//берем функцию для отправки почты

        $messages  = array();
		$notFounds = 0;
        $jsonError = false;
        $updated   = 0;

        $logger->info("Start cabinet:sapAutoSupply");

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
            $msg = "В Autosupply.json неверные данные";
            $output->writeln($msg);
            $logger->info($msg);
            $messages[] = $msg;
            $jsonImport->sendEmail($messages, $this->file);
            return false;
        }

        //$output->writeln(sprintf('Количество строк в файле: %d', count($json)));
        $output->writeln(sprintf('count strings in file: %d', count($json)));
        $logger->info("cabinet:sapAutoSupply Всего в файле: " . count($json));

        foreach ($json as $key => $res) {

            /** @var $product Product */
            $cabinetorder = $repository->find($res->Id);
            if ($cabinetorder) {
				$supply = $repositorySupply->findOneByCabinetorder($cabinetorder);
				if ($supply) {
					$type = 'supply';

					if(isset($res->Sapid)){
						if( $res->Status == null || $res->Status == '' || $res->Status == 0 )
							$res->Status = 0;

						if( ( $res->Sapplyid == '' || $res->Sapplyid == null ) && ( $res->Status == null || $res->Status == '' ) )
							$res->Status = 999;

						//if ($res->Status != $cabinetorder->getStatussupply()->getId())
							//$change_status_order = true;

						$statussupply = $repStatussupply->findOneBySapid($res->Status);
						$supply->setStatussupply($statussupply);

						if( isset($res->Sapplyid) )
							$supply->setSapid($res->Sapplyid);

						if( $statussupply->getSapid() == 98 || $statussupply->getSapid() == 0 ){
							$status = $repStatus->find(5);
							$cabinetorder->setStatus($status);
							$type = 'supplyok';
						}

						//$output->writeln(sprintf('Заказ: %d', $Cabinetorder->getId(), ' Sapid: %d', $Cabinetorder->getSapid(), ' Status Supply: %d', $res->Status ));
						$output->writeln(sprintf('Order: %d', $cabinetorder->getId(), ' Sapid: %d', $cabinetorder->getSapid(), ' Status Supply: %d', $res->Status ));
						$updated++;
					}
					else{
						$statussupply = $repStatussupply->find(4);
						$supply->setStatussupply($statussupply);
					}
					$em->flush();
					
					if( $cabinetorder->getSentmail() == 1 )
						$j3importer->sendEmailChangeStatusOrder($cabinetorder, $type );
				}
				else
					$notFounds++;

			}
			else
				$output->writeln(sprintf('Order: %d', $res->Id, 'not fount'));
				//$output->writeln(sprintf('Заказ: %d', $res->Id, 'не найден'));
		}
        //$output->writeln(sprintf('Обработано поставок: %d', $index));
        $output->writeln(sprintf('processed supply: %d', $updated));
        //$em->flush();
        $em->clear();

        //$output->writeln('Конец загрузки из autosupply.json');
        $output->writeln('End load from suppsap.json');
        //$output->writeln(sprintf('Заказов обновлено: %d', $updated));
        $output->writeln(sprintf('Status supply updatet: %d', $updated));
        
        
		$logger->info("КОНЕЦ ЗАГРУЗКИ ИЗ AUTOSUPPLY.JSON; Cтатусов поставки обновлено: " . $updated . '; Не найдено: ' . $notFounds );
        $messages[] = "Всего в файле: " . count($json) . "; Cтатусов поставки обновлено: " . $updated . '; Не найдено: ' . $notFounds;
		
		$jsonImport->sendEmail($messages, $this->file);

        if ($env != "dev") {
            unlink($this->file);
        }
    }


}