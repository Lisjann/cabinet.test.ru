<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;
use Top10\CabinetBundle\Entity\Product;
use Top10\CabinetBundle\Entity\ProductReserve;
use Top10\CabinetBundle\Entity\User;
use Top10\CabinetBundle\Service;

class Sap11Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:sap11')
            ->setDescription('Сохранение резервов заказов');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("НАЧАЛО ЗАГРУЗКИ ИЗ 11.JSON");

        $container = $this->getContainer();
        $finder = new Finder();
        $dir = sprintf('%s/../var/sap/', $this->getContainer()->getParameter('kernel.root_dir'));
        $em = $container->get('doctrine')->getManager();
        /** @var $jsonImport Service\JsonImport */
        $jsonImport = $container->get('cabinet.json_import');
        $err_catch = array();
        $finder->name('11.json');
        $finder->in($dir);

        if (!$finder->count()) {
            $output->writeln('<error>Файл не найден</error>');
            exit;
        }

        $output->writeln('<info>Файл найден</info>');
        $res_rep = $em->getRepository('Top10CabinetBundle:ProductReserve');
        $user_rep = $em->getRepository('Top10CabinetBundle:User');
        $product_rep = $em->getRepository('Top10CabinetBundle:Product');

        foreach ($finder as $file) {/** @var \Symfony\Component\Finder\SplFileInfo $file */
            /*
             * Проверяем валидность самого файла
             */
            $json = $jsonImport->jsonValidate($file->getContents(), $jsonError);

            if ($jsonError) { // Если файл не валиден отправ
                $output->writeln(sprintf('<error>%s</error>', $jsonError));
                $err_catch[] = "File 11.json corrupted. Error: " . $jsonError;
                $jsonImport->handleException(
                    new \ErrorException(implode(PHP_EOL, $err_catch)), $file->getRealPath()
                );
                continue;
            }

            $output->writeln(sprintf('<info>Найдено %s записей</info>', count($json)));

            $types = array(
                '14' => array(
                    'type' => 'tire',
                    'name' => 'шинам'),
                '41' => array(
                    'type' => 'disk',
                    'name' => 'дискам')
            );
            $removed = array();
            foreach ($json as $res) {
                /*
                 * Проверим элемент на валидность, что в нем присутствуют все поля
                 */
                $valid = $this->validateJson($res);
                if ($valid !== true) {
                    $str = '';
                    if ($res) {
                        $str = implode('; ', (array)$res);
                    }
                    $err_catch[] = sprintf('Не валидная запись в json [%s]. Не хватает полей [%s]', $str, implode('; ', $valid));
                    $output->writeln(sprintf('<error>Не валидная запись в json [%s]</error>', $str));
                    continue;
                }

                if (!in_array($res->Type,$removed)) {
                    if (isset($types[$res->Type])) {
                        $type = $types[$res->Type]['type'];
                        $removed[] = $res->Type;
                        $output->writeln(sprintf('Удаляем существующие записи по %s', $types[$res->Type]['name']));
                        $res_rep->removeByType($type);
                    }
                }

                $output->writeln(sprintf('Обрабатываю %s', $res->Article));
                /** @var User $user */
                $user = $user_rep->findOneBy(array('sapid' => $res->Userid));
                if (!$user) {
                    $output->writeln(sprintf('<error>Не найден пользователь с sap_id %s</error>', $res->Userid));
                    continue;
                }

                $reserve = new ProductReserve();
                $reserve->setArticle($res->Article);
                $reserve->setUserSapId($res->Userid);
                $reserve->setUser($user);
                $reserve->setGroup($res->Group);
                $reserve->setProductName(base64_decode($res->Product));

                /** @var Product $product */
                $product = $product_rep->findOneBy(array('article' => $res->Article));
                if ($product) {
                    $reserve->setProduct($product);
                }

                switch ($res->Type) {
                    case "41":
                        $reserve->setType('disk');
                        break;
                    case "14":
                        $reserve->setType('tire');
                        break;
                }
                $reserve->setCapacity($res->Capacity);
                $reserve->setReserve($res->Reserve);

                $em->persist($reserve);
            }
            $output->writeln('<info>Сохраняю данные</info>');
            $em->flush();

            // отправка уведомления на email
            $output->writeln('<info>Обрабатываем результат, отправляем почту</info>');
            if ($err_catch) {
                $jsonImport->handleException(
                    new \ErrorException(implode(PHP_EOL, $err_catch)), $file->getRealPath()
                );
            } else {
                $jsonImport->handleSuccess($err_catch, $file->getRealPath());
                unlink($file->getRealPath());
            }
        }

        $output->writeln("КОНЕЦ ЗАГРУЗКИ ИЗ 11.JSON");
    }

    /**
     * Валидная структура json. Вся поля должны присутсвовать и быть не пустыми
     *
     * <pre>
     * {
     *  "Userid":"204682",
     *  "Type":"14",
     *  "Group":"ШиныЗимниеGislaved",
     *  "Article":"К23829",
     *  "Product":"0JDQstGC0L7RiNC40L3QsCBHaXNsYXZlZCAxNzUvNjUgUjE0IDgyVCBORjVERCA=",
     *  "Reserve":"4",
     *  "Capacity":"0.240"
     * }
     * </pre>
     * @param $res
     * @return array|bool
     */
    private function validateJson($res)
    {
        $properties = array('Userid', 'Type', 'Group', 'Article', 'Product', 'Reserve', 'Capacity');
        $error_properties = array();
        foreach ($properties as $property) {
            if (!property_exists($res, $property) || !$res->$property) {
                $error_properties[] = $property;
            }
        }

        if (empty($error_properties)) {
            return true;
        } else {
            return $error_properties;
        }
    }

}
