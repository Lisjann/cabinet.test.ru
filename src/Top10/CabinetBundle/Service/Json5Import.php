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

class Json5Import extends JsonImport
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
            'new_users' => array(),
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

            $isNewUser = $user == null;
            $email = trim(mb_strtolower( $res->Email ));
            $sapid = (int)$res->Sapid;

			if ($isNewUser)
			{
				echo  'New User sapid - ' . $sapid . ' email - ' . $email . '; ';
                $user = $this->um->findUserByEmail($email);
                if($user !== null && !$user->getNew()) {
                    $msg = sprintf("Попытка использовать существующий емейл при создании пользователя с Sapid '%s'", $sapid);
                    $result['messages'][] = $msg;
                    $this->logger->info($msg);
                    continue;
                } elseif ($user === null){
                    $user = $this->um->createUser();
                }

                $password = $this->generatePass();
                $user->setUsername($email);
                $user->setEmail($email);
                $user->setPlainPassword($password);
                $user->setSapid($sapid);
                $user->setEnabled(true);
            }
			else
				echo  'Update User sapid - ' . $sapid . ' email - ' . $user->getEmail() . '; '; 

            $user->setFullName( base64_decode(trim($res->Username)) );

            // Тип привязанного прайса по шинам
            if( property_exists($res, 'Typeprice14') ) {
                $t = trim($res->{'Typeprice14'});
                if( in_array($t, array('01','02','03','04','05')) ) {
                    $user->setTypeprice14($t);
                }
            }

            // Тип привязанного прайса по дискам
            if( property_exists($res, 'Typeprice41') ) {
                $t = trim($res->{'Typeprice41'});
                if( in_array($t, array('01','02','03','04','05')) ) {
                    $user->setTypeprice41($t);
                }
            }

            try {
				$user->setAccountDisk((float)$res->AccountDisk);
                $user->setAccountTier((float)$res->AccountTier);
                $user->setLimitcreditDisk((float)$res->LimitcreditDisk);
                $user->setLimitcreditTier((float)$res->LimitcreditTier);
                $user->setNumberdocDisk(base64_decode(trim($res->NumberdocDisk)));
                $user->setNumberdocTier(base64_decode(trim($res->NumberdocTier)));
                $user->setEmailmanagerDisk(trim($res->EmailmanagerDisk));
                $user->setEmailmanagerTier(trim($res->EmailmanagerTier));
                $user->setDatelastpayDisk(new \DateTime("@" . trim($res->DatelastpayDisk, "-")));
                $user->setDatelastpayTier(new \DateTime("@" . trim($res->DatelastpayTier, "-")));
                $user->setCompany(base64_decode(trim($res->Company)));
                $user->setTelephone(base64_decode(trim($res->Telephone)));
                $user->setNew(false);
            }
            catch (\Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
                $result['messages'][] = $e->getMessage() . ". Sapid:" . $res->Sapid;
                $this->logger->err($e->getMessage() . " Sapid:" . $res->Sapid);
                continue;
            }

            if( $isNewUser ) {
                $this->logger->info(
                    sprintf('user with email "%s" created.', $user->getEmail())
                );
                $result['new_users'][] = array(
                    'user' => $user,
                    'pass' => $user->getPlainPassword(),
                );
            }

            $this->um->updateUser($user);

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

        $this->handleSuccess($result['messages'], $this->file);
        $this->sendUsersCredentials($result['new_users']);

        // delete file
        if ($env === "prod") {
            unlink($this->getFilePath());
        }

        return;
    }

    /**
     * @return string
     */
    private function generatePass()
    {
        // Символы, которые будут использоваться в пароле.
        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        // Количество символов в пароле.
        $max = 10;
        // Определяем количество символов в $chars
        $size = strlen($chars) - 1;
        // Определяем пустую переменную, в которую и будем записывать символы.
        $password = null;
        // Создаём пароль.
        while ($max--)
            $password .= $chars[rand(0, $size)];
        return $password;
    }

    private function sendUsersCredentials(array $users)
    {
        $container = $this->kernel->getContainer();

        /** @var User $user */
        foreach($users as $userArr) {
            $user = $userArr['user'];
            /** @var $message \Swift_Mime_Message */
            $message = \Swift_Message::newInstance()
                ->setSubject('Ваша заявка на регистрацию одобрена')
                ->setContentType("text/html")
                ->setFrom($container->getParameter('top10_cabinet.emails.default'))
                ->setTo($user->getEmail())
                ->setBody($this->templating->render('Top10CabinetBundle:Mail:SuccessNewUser.html.twig', array(
                    'user' => $user,
                    'pass' => $userArr['pass']
            )));
            $container->get('mailer')->send($message);
        }
    }
}