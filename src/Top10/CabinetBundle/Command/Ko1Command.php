<?php 
namespace Top10\CabinetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Top10\CabinetBundle\Entity\User;

class Ko1Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cabinet:ko1')
            ->setDescription('Новые заявки на регистрацию')
//             ->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
//             ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$container = $this->getContainer();
    	$em = $container->get("doctrine")->getEntityManager();
        /** @var $users User[] */
    	$users = $this->getContainer()->get("doctrine")
	    	->getRepository('Top10CabinetBundle:User')
	    	->findByNew(true);
    	
    	$daylife = $container->getParameter('top10_cabinet.day_live')*60*60*24;
    	
    	if(is_array($users)) {
    		$this->getContainer()->get('logger')->info("НАЧАЛО ВЫГРУЗКИ В 1.JSON");
    		$userjson = array();
    		foreach ($users as $user){
	    		$userjson[] = array(
	    			"Id"	=> $user->getId(),
	    			"Fio" => $user->getUsername(),
	    			"Company" => $user->getCompany(),
	    			"Email" => $user->getEmail(),
	    			"Tel" => $user->getTelephone(),
	    			"Message" => $user->getMessage()
	    		);
	    		$this->getContainer()->get('logger')->info(
	    			sprintf('[1.JSON] New user registration: %s', $user)
	    		);
	    		
	    		if(time() - $user->getCreated()->getTimestamp() >= $daylife){
	    			$em->remove($user);
	    			$em->flush();
	    		}
	    		
	    	}
	    	file_put_contents("var/ko/1.json", json_encode($userjson));
	    	$this->getContainer()->get('logger')->info("КОНЕЦ ВЫГРУЗКИ В 1.JSON");
    	}
    }
}
