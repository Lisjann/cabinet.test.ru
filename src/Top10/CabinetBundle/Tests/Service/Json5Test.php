<?php

namespace Top10\CabinetBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager;

use Top10\CabinetBundle\Classes\JsonImportException;
use Top10\CabinetBundle\Service\Json5Import;
use Top10\CabinetBundle\EventListener\EmailListener;

/**
 * phpunit -c app src/Top10/CabinetBundle/Tests/Service/Json5Test.php
 */
class Json5Test extends WebTestCase
{
    /**
     * @var Json5Import
     */
    protected $importer;
    protected $container;

    // вызов перед каждым тестом
    protected function setUp()
    {
        $client = static::createClient();
        $container = $client->getKernel()->getContainer();
        /** @var $importer Json5Import */
        $importer = $container->get('cabinet.json5_import');
        $importer->setJsondir("var/test/");

        $this->importer = $importer;
        $this->container = $container;
    }

    /**
     * Проверка ошибки на дублирование емейл
     * Создаем нового пользователя в базе без sapid, пытаемся через обновление создать нового пользователя
     * с таким же емейлом
     * Проверяем что отправилсь почта
     */
    public function testExistedUserEmail()
    {
        $container = $this->container;
        /** @var $um UserManager */
        $um = $container->get("fos_user.user_manager");
        
        $plugin = new EmailListener();        
        $mailer = $container->get('mailer');
        $mailer->registerPlugin($plugin);

        $user = new \Top10\CabinetBundle\Entity\User();
        $user->setUsername("testExistedUserEmail");
        $user->setPassword("testExistedUserEmail");
        $user->setEmail("testExistedUserEmail@test.com");
        $user->setSapid(null);
        $um->updateUser($user);

        $jsonString = '[
            {
                "Id":"10",
                "Sapid":"100000000000",
                "AccountDisk":"627433.38",
                "AccountTier":"1682258",
                "LimitcreditDisk":"20000000.00",
                "LimitcreditTier":"15000000.00",
                "NumberdocDisk":"0J3QvtC80LXRgCDQtNC+0LPQvtCy0L7RgNCwINC00LjRgdC60Lg=",
                "NumberdocTier":"0J3QvtC80LXRgCDQtNC+0LPQvtCy0L7RgNCwINGI0LjQvdGL",
                "EmailmanagerDisk":"testExistedUserEmail@test.com",
                "EmailmanagerTier":"testExistedUserEmail@test.com",
                "DatelastpayDisk":"1299024000",
                "DatelastpayTier":"1299024000",
                "Username":"0JrQsNGA0L/QvtCy0LAg0JXQu9C10L3QsA==",
                "Email":"testExistedUserEmail@test.com",
                "Company":"0J7QntCeICLQotC+0L8t0KLQtdC9Ig==",
                "Telephone":"KzcgOTUwIDIwNSAzOSA5OQ=="
            }
        ]';

        try {
            // запускаем импорт
            $this->importer->import($jsonString);
        }
        catch (JsonImportException $e) {
            $this->fail( $e->getMessage() );
        }
        $um->deleteUser($user);

        $messageFound = false;
        // ищем в отправленных наш email
        $pattern = '#Попытка использовать существующий емейл при создании пользователя#ui';
        foreach ($plugin->getMessages() as $message) {
            $body = $message->getBody();
            if( $body && preg_match($pattern, $body) == 1 ) {
                $messageFound = true;
            }
        }

        $this->assertTrue( $messageFound );
    }
}
