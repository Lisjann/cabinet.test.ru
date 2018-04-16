<?php
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Service\Json3Import;
use Monolog\Logger;

use Symfony\Component\HttpKernel\Kernel;

class sendTestEmailCommand extends ContainerAwareCommand
{
    /**
     * php app/console --env=dev cabinet:sendtestemail
     */
    protected function configure()
    {
        $this
            ->setName('cabinet:sendtestemail')
            ->setDescription('отправка почты тест');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var $logger Logger */
        $logger = $container->get('logger');
        
		
		$message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('Lisjann@mail.ru')
        ->setTo('shirockovan@gmail.com')
        ->setBody('Hello');

		$container->get('mailer')->send($message);

        $output->writeln('mail send');

		$logger->info("ОТПАРАВКА ТЕСТОВОГО ПИСЬМА");

        return 1;
    }

}