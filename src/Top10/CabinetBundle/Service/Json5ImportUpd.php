<?php

namespace Top10\CabinetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\TwigBundle\TwigEngine;
use FOS\UserBundle\Doctrine\UserManager;

use Top10\CabinetBundle\Classes\JsonImportException;
use Top10\CabinetBundle\Service\JsonImport;
use Top10\CabinetBundle\Entity\User;

class Json5ImportUpd extends JsonImport
{
    protected $em;
    protected $um;
    protected $templating;

    public function __construct(EntityManager $em, Logger $logger, Kernel $kernel, UserManager $um, TwigEngine $templating)
    {
        parent::__construct($kernel, $logger, 'sap/5.json');

        $this->em = $em;
        $this->um = $um;
        $this->templating = $templating;
    }

    /**
     * Обработка файла
     *
     * @throws \Top10\CabinetBundle\Classes\JsonImportException
     * @return array
     */
    public function parse($jsonString)
    {
        $messages = array();
        $result = array(
            'result' => false,
            'messages' => $messages,
            'countusers' => 0,
            'countnotusers' => 0,
        );

        $json = $this->jsonValidate($jsonString, $jsonError);
        if ($jsonError) {
            throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_VALID, $jsonError);
        }

        if (!is_array($json)) {
            throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_ARRAY);
        }

        foreach ($json as $res) {

            if (!isset($res->Sapid)) {
                $result['messages'][] = "Нет Sapid партнера 5.json";
                $this->logger->info("Нет sap id партнера 5.json");
                continue;
            }
            if (!isset($res->Email)) {
                $result['messages'][] = "Нет Email партнера 5.json";
                $this->logger->info("Нет email партнера 5.json");
                continue;
            }
            if (!isset($res->Username)) {
                $result['messages'][] = "Нет Username партнера 5.json";
                $this->logger->info("Нет username партнера 5.json");
                continue;
            }

            /** @var $user User */
            $user = $this->um->findUserBy(array('sapid' => $res->Sapid+0));


			if( $user ){
				$result['countusers']++;

				$sapid = (int)$res->Sapid;
				
				$user->setEnabled(true);


				// Тип привязанного прайса по шинам
				if( property_exists($res, 'Typeprice14') ) {
					$t = trim($res->{'Typeprice14'});
					//Все шини теперь продаются по Typeprice14 = 02 поэтому если Typeprice14 пустой то ставим 02
					if( $t == '' || $t == null )
						$user->setTypeprice14('02');
					else
						if( in_array($t, array('01','02','03','04','05')) ) {
							$user->setTypeprice14($t);
						}
				}


				// Тип привязанного прайса по дискам
				if( property_exists($res, 'Typeprice41') ) {
					$t = trim($res->{'Typeprice41'});
					if( $t == '' || $t == null )
						$user->setTypeprice41('02');
					else
						if( in_array($t, array('01','02','03','04','05')) ) {
							$user->setTypeprice41($t);
						}
				}

				try {
					//$user->setEmail($res->Email);//когда все почты будут синхронизированны с САПом
					$user->setAccountDisk((float)$res->AccountDisk);
					$user->setAccountTier((float)$res->AccountTier);
					$user->setLimitcreditDisk((float)$res->LimitcreditDisk);
					$user->setLimitcreditTier((float)$res->LimitcreditTier);
					$user->setNumberdocDisk(base64_decode(trim($res->NumberdocDisk)));
					$user->setNumberdocTier(base64_decode(trim($res->NumberdocTier)));
					if( $res->EmailmanagerDisk != "" )
						$user->setEmailmanagerDisk(trim($res->EmailmanagerDisk));
					if( $res->EmailmanagerTier != "" )
						$user->setEmailmanagerTier(trim($res->EmailmanagerTier));
					$user->setDatelastpayDisk(new \DateTime("@" . trim($res->DatelastpayDisk, "-")));
					$user->setDatelastpayTier(new \DateTime("@" . trim($res->DatelastpayTier, "-")));
					if( $res->Company != "" )
						$user->setCompany(base64_decode(trim($res->Company)));
					if( $res->Telephone != "" )
						$user->setTelephone(base64_decode(trim($res->Telephone)));
					$user->setNew(false);
				}
				catch (\Exception $e) {
					echo 'Caught exception: ', $e->getMessage(), "\n";
					$result['messages'][] = $e->getMessage() . ". Sapid:" . $res->Sapid;
					$this->logger->err($e->getMessage() . " Sapid:" . $res->Sapid);
					continue;
				}

				$this->um->updateUser($user);
			}
			else
				$result['countnotusers']++;

        }

        return $result;
    }

    /**
     * Импортировать 5.json
     * Обертка для @see parse
     *
     * @param null $jsonString
     * @throws \Top10\CabinetBundle\Classes\JsonImportException
     * @return void
     */
    public function import($jsonString = null)
    {
        $env = $this->kernel->getEnvironment();

        try {
            if($jsonString === null) {
                if( !file_exists($this->getFilePath()) ) {
                    throw new JsonImportException(JsonImportException::ERROR_JSON_NOT_FOUND);
                }

                $jsonString = file_get_contents($this->getFilePath());
            }

            // parse json
            $result = $this->parse($jsonString);
        }
        catch (JsonImportException $e) {
            $this->handleException($e, $this->getFilePath());
            return;
        }

        //$this->handleSuccess($result['messages'], $this->file);

        // delete file
        if ($env === "prod") {
           unlink($this->getFilePath());
        }

        return $result;
    }
}