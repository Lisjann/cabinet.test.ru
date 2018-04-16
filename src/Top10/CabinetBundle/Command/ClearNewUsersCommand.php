<?php
/**
 * ClearNewUsersCommand.php 
 * Created by Anton Tsyrulnik.
 *
 * Date: 24.04.13
 * Time: 15:30
 */

namespace Top10\CabinetBundle\Command;


use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\User;

class ClearNewUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("cabinet:clear-users")
            ->setDescription("Очищает список пользователей, отправивших заявку, но не попавших за месяц в 5json");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("cabinet:clear-users begin");

        $container = $this->getContainer();
        /** @var $em EntityManager */
        $em = $container->get('doctrine')->getManager();
        /** @var $logger Logger */
        $logger = $container->get('logger');

        $user_rep = $em->getRepository('Top10CabinetBundle:User');

        $period = new \DateTime('now');
        $period->sub(new \DateInterval('P1M'));
        /** @var $users User[] */
//        $users = $user_rep->findBy(array('new'=>true, 'created'=>$period));

        $qb = $em->createQueryBuilder();

        $qb
            ->select('u')
            ->from('Top10CabinetBundle:User', 'u')
            ->where('u.new = :new')
                ->setParameter('new', true)
            ->andWhere('u.created < :period')
                ->setParameter('period', $period);

        $users = $qb->getQuery()->getResult();

        if ($users){
            $output->writeln(sprintf("Найдено %s просроченных заявок.", count($users)));
            foreach($users as $user){
                $em->remove($user);
                $str = sprintf("Пользователь %s был удален.", $user->getEmail());
                $output->writeln($str);
                $logger->info($str);
            }
            $em->flush();
        } else {
            $output->writeln("Просроченных заявок на регистрацию не найдено");
        }

        $output->writeln("cabinet:clear-users end");
    }

}