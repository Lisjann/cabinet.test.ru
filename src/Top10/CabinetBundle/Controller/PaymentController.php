<?php

namespace Top10\CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Top10\CabinetBundle\Entity\Payment;
use Top10\CabinetBundle\Form\PaymentType;
use Top10\CabinetBundle\Service\CartManager;

/**
 * Payment controller.
 *
 * @Route("/payment")
 */
class PaymentController extends Controller
{
    /**
     * Lists all Payment entities.
     *
     * @Route("/", name="payment")
     * @Template()
     */
    public function indexAction()
    {
    	$request 		= $this->getRequest();
    	$paginator 		= $this->get('knp_paginator');
    	$security 		= $this->get('security.context');
    	$current_user 	= $security->getToken()->getUser();
    	$result 		= array();
    	if($current_user != "anon."){
    		
    		$where_params 	= array("p.user = :user");
    		$pre_params		= array('user' => $current_user->getId());
    		if($request->query->get("type")){
    			$where_params[] = "p.type = :type";
    			$pre_params = array_merge($pre_params, array("type" => $request->query->get("type")));
    		}

	        $repository 	= $this->getDoctrine()->getRepository('Top10CabinetBundle:Payment');
	        $query 			= $repository->createQueryBuilder('p')
	        					->where(implode(" AND ", $where_params))
	        					->setParameters($pre_params)
	        					->orderBy('p.data', 'DESC')
	        ;
	    	$pagination = $paginator->paginate(
	    		$query->getQuery(),
	    		$this->get('request')->query->get('page', 1)/*page number*/,
	    		30/*limit per page*/
	    	);
	    	
	    	$result = array(
	    		'entities' 	=> $pagination
	    	);
            $cartObj = $this->get('cabinet.cart_manager');
	    	$cart = $cartObj->get();
	    	$result = array_merge($result, array(
	    		"cartinfo" => $cart
	    	));
    	}
        
    	return $result;
    	
    }

    /**
     * Finds and displays a Payment entity.
     *
     * @Route("/{id}/show", name="payment_show")
     * @Template()
     */
    private function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('Top10CabinetBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Payment entity.
     *
     * @Route("/new", name="payment_new")
     * @Template()
     */
    private function newAction()
    {
        $entity = new Payment();
        $form   = $this->createForm(new PaymentType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Payment entity.
     *
     * @Route("/create", name="payment_create")
     * @Method("post")
     * @Template("Top10CabinetBundle:Payment:new.html.twig")
     */
    private function createAction()
    {
        $entity  = new Payment();
        $request = $this->getRequest();
        $form    = $this->createForm(new PaymentType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Payment entity.
     *
     * @Route("/{id}/edit", name="payment_edit")
     * @Template()
     */
    private function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('Top10CabinetBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $editForm = $this->createForm(new PaymentType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Payment entity.
     *
     * @Route("/{id}/update", name="payment_update")
     * @Method("post")
     * @Template("Top10CabinetBundle:Payment:edit.html.twig")
     */
    private function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('Top10CabinetBundle:Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment entity.');
        }

        $editForm   = $this->createForm(new PaymentType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Payment entity.
     *
     * @Route("/{id}/delete", name="payment_delete")
     * @Method("post")
     */
    private function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('Top10CabinetBundle:Payment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Payment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('payment'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
