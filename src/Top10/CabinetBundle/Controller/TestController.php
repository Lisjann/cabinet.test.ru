<?php
namespace Top10\CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Top10\CabinetBundle\Entity\ProductReserve;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/test")
 */
class TestController extends Controller
{
    /**
     * @Route("/", name="test_index")
     * @Template()
     * @Secure(roles="ROLE_USER")
     */
	public function indexAction()
		{

			$security = $this->get('security.context');
			$user = $security->getToken()->getUser();
			 $em = $this->getDoctrine()->getManager();
			$res_rep = $em->getRepository('Top10CabinetBundle:ProductReserve');
			$reserves = $res_rep->findBy(array('user' => $user));
			
			foreach ($reserves as $reserv) {
				switch ($reserv->getType()){
					case 'tire':
						$reerves_ar['tire'][] = $reserv->getProductName();
						break;
					case 'disk':
						$reerves_ar['disk'][] = $reserv->getProductName();
						break;
				}
			}

			return new Response('<html><body> '. print_r($reerves_ar) .'</body></html>');
			//return $this->render('Top10CabinetBundle:Test:index.html.twig', array('name' => $user));
		}
}