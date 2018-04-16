<?php
namespace Top10\CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Top10\CabinetBundle\Entity\ProductReserve;

/**
 * @Route("/reserve")
 */
class ReserveController extends Controller
{
    /**
     * @Route("/", name="reserve_index")
     * @Template()
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction(Request $request)
    {
        $security = $this->get('security.context');
        $user = $security->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $res_rep = $em->getRepository('Top10CabinetBundle:ProductReserve');
        /** @var ProductReserve[] $reserves */
        $reserves = $res_rep->findBy(array('user' => $user));


        /** @var $catalogSearch \Top10\CabinetBundle\Service\CatalogSearch */
        $catalogSearch = $this->get('cabinet.catalog_search');
        $sResult = $catalogSearch->search(false, true, true);

        $cartManager = $this->get('cabinet.cart_manager');
        $reerves_ar = array(
            'tire' => array(),
            'disk' => array()
        );

        foreach ($reserves as $reserv) {
            switch ($reserv->getType()){
                case 'tire':
                    $reerves_ar['tire'][] = $reserv;
                    break;
                case 'disk':
                    $reerves_ar['disk'][] = $reserv;
                    break;
            }
        }

        $result = array(
            'filterForm' => $sResult['filterForm']->createView(),
            'price_range' => $sResult['filterResult']['prices'],
            'catalogFilter' => $sResult['catalogFilter'],
            'cartinfo' => $cartManager->getUserCartInfo(),
            'reserves' => $reerves_ar
        );

        return $result;
    }
}