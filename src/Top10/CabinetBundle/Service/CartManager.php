<?php

namespace Top10\CabinetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Top10\CabinetBundle\Entity\Cart;
use Top10\CabinetBundle\Entity\Product;
use Top10\CabinetBundle\Entity\ProductRepository;

class CartManager
{
    /** @var $user \Top10\CabinetBundle\Entity\User */
    private $user;
    private $security;
    private $em;

    public function __construct(SecurityContext $security, EntityManager $em)
    {
        $this->security = $security;
        $this->user = $security->getToken()->getUser();
        $this->em = $em;
    }

    /**
     * @deprecated
     *
     * @return array
     */
    public function get()
    {
        return $this->getUserCartInfo();
    }

    /**
     * Информация о корзине
     *
     * @return array
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getUserCartInfo()
    {
        if ($this->security->isGranted("ROLE_USER")) {
            $tire_quantity = 0;
            $tire_price = 0;
            $disk_quantity = 0;
            $disk_price = 0;
            /** @var  $items Cart[] */
            $items = $this->user->getCarts();
            if (is_object($items) && count($items)) {
                foreach ($items as $item) {
                    if ($item->getProduct()->getType() == "tire") {
                        $tire_quantity += $item->getQuantity();
                        $tire_price += $item->getPrice() * $item->getQuantity();
                    }
                    if ($item->getProduct()->getType() == "disk") {
                        $disk_quantity += $item->getQuantity();
                        $disk_price += $item->getPrice() * $item->getQuantity();
                    }
                }
            }
            return array(
                "tire_quantity" => $tire_quantity,
                "tire_price" => number_format($tire_price, 2, ".", " "),
                "disk_quantity" => $disk_quantity,
                "disk_price" => number_format($disk_price, 2, ".", " ")
            );
        }
        else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @TODO Переделать все нахрен
     * Сюда должен прихродить Product и count
     * Валидацией это заниматься не должно.
     * !!!!!!!
     *
     * @param $id
     * @param $count
     * @return array
     */
    public function addToCard($id, $count)
    {
        $em = $this->em;

        /** @var  $repository ProductRepository */
        $repository = $em->getRepository('Top10CabinetBundle:Product');

        /** @var  $repCart \Doctrine\ORM\EntityRepository */
        $repCart = $em->getRepository('Top10CabinetBundle:Cart');

        $count = (int) $count;
        /** @var  $product Product */
        $product = $repository->find($id);

        if( $count <= 0 || $product === null ) {
            return null;
        }

        $result = array(
            "added" => array(),
            "wo_price" => array()
        );

        $price = $product->getPriceForUser($this->user);
        //if( $price > 0 ) {
            /** @var $cart Cart */
            $cart = $repCart->findOneBy( array(
                'product' => $product->getId(),
                'user' => $this->user->getId(),
            ));

            if( $cart !== null ) {
                $cart->setPrice($price);
                $cart->setQuantity($cart->getQuantity() + $count);
            }
            else {
                $cart = new Cart();
                $cart->setUser($this->user);
                $cart->setProduct($product);
                $cart->setPrice($price);
                $cart->setQuantity($count);
                $em->persist($cart);
            }

            $em->flush();

            $result["added"][] = $product;

        /*}
        else { // Наполняем масив товаров у которых не оказалось цены.
            $result["wo_price"][] = $product;
        }*/

        return $result;
    }


}