<?php

namespace Top10\CabinetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Knp\Component\Pager\Paginator;

use Top10\CabinetBundle\Entity\ProductRepository;
use Top10\CabinetBundle\Form\Model\CatalogFilter;
use Top10\CabinetBundle\Form\CatalogFilterForm;

use Top10\CabinetBundle\Entity;

class CatalogSearch
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;
    /**
     * @var EntityManager $em
     */
    protected $em;
    /**
     * @var Paginator $paginator
     */
    protected $paginator;
    /**
     * @var Request $request
     */
    protected $request;
    /**
     * @var FormFactory $formFactory
     */
    protected $formFactory;

	public function __construct(ContainerInterface $container)
	{
		$this->em = $container->get('doctrine.orm.entity_manager');
		$this->paginator = $container->get('knp_paginator');
		$this->request = $container->get('request');
        $this->formFactory = $container->get('form.factory');
        $this->container = $container;
	}

    public function search($excludeProducts = false, $countAbsPrices = true, $countCPrices = true)
    {
        /** @var $productRep ProductRepository */
        $productRep = $this->em->getRepository('Top10CabinetBundle:Product');

        /** @var $security SecurityContext */
        $security = $this->container->get('security.context');
        /** @var $current_user Entity\User */
        $current_user = $security->getToken()->getUser();

		//СОРТИРОВКА! перенос Реквестов в функцию getProductsEx при помощи массива $catalogSort.
		//Почемуто pagination сам не хочет обрабатывать свою же сортировку как в заказах
		$catalogSort = array();

		if( $this->request->get('sort') )
			$catalogSort['sort'] = $this->request->get('sort'); 

		if( $this->request->get('direction') )
			$catalogSort['direction'] = $this->request->get('direction'); 

		$catalogFilter = new CatalogFilter();
        // можно на preBind у формы это все сделать
        $f = $this->request->get('f', array());
        if(isset($f['type'])) {
            $catalogFilter->setType($f['type']);
        }

        $catalogFilter->setPriceType('disk', $current_user->getTypeprice41());
        $catalogFilter->setPriceType('tire', $current_user->getTypeprice14());

        $filterForm = $this->formFactory->create(new CatalogFilterForm(array('empty_form' => true)), $catalogFilter);

        $filterForm->bind($this->request);
        if( !$excludeProducts ) {
            $filterResult = $productRep->getProductsEx( $catalogFilter, $excludeProducts, $countAbsPrices, $countCPrices, $catalogSort );
            $products = $filterResult['products'];
            # recreate form to fullfill filters
            $filterForm = $this->formFactory->create(new CatalogFilterForm(array('empty_form' => false)), $catalogFilter);

            $pagination = $this->paginator->paginate(
                $products,
                $this->request->query->get('page', 1), 60
            );
			$pagination->setTemplate('Top10CabinetBundle:Pagination:pagination.html.twig');
			$pagination->setSortableTemplate('Top10CabinetBundle:Pagination:sortable.html.twig');

            $result = array(
                'cntPagination' => count($products),
				'pagination' => $pagination,
                'filterForm' => $filterForm,
                'filterResult' => $filterResult,
                'catalogFilter' => $filterForm->getData(),
            );
        }
        else {
            $filterResult = $productRep->getProductsEx($catalogFilter, $excludeProducts, $countAbsPrices, $countCPrices, $catalogSort);

            $result = array(
                'filterForm' => $filterForm,
                'filterResult' => $filterResult,
                'catalogFilter' => $filterForm->getData(),
            );
        }

        return $result;

    }

}