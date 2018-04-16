<?php

namespace Top10\CabinetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SupplyForm extends AbstractType
{
	protected $order;
	protected $deliverytypes;
	protected $disabled;
	protected $deliverytypesRequest;

    public function __construct($order, $deliverytypes, $disabled=false, $deliverytypesRequest=null)
    {
        $this->order = $order;
        $this->deliverytypes = $deliverytypes;
        $this->disabled = $disabled;
        $this->deliverytypesRequest = $deliverytypesRequest;
    }


	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		/** @var $Supply Supply */
		//$deliverytype = $this->getDoctrine()->getRepository('Top10CabinetBundle:Deliverytype')->findAll();
		$order = $this->order;
		$deliverytypes = $this->deliverytypes;
		$disabled = $this->disabled;
		$deliverytypesRequest = $this->deliverytypesRequest;



		if( $deliverytypesRequest == null ){
			if( $order->getSupply() )
				$deliverytypesRequest = $order->getSupply()->getDeliverytype()->getId();
		}



		$choices = array();
		foreach( $deliverytypes as $deliverytype ){
			$choices[ $deliverytype['id'] ] = $deliverytype['name'];
		}

		$deliverytypeint = array( 'label' => 'Способ доставки', 'choices' => $choices, 'required' => true, 'disabled' => $disabled );
		$datedo = array( 'label' => 'Дата отгрузки', 'input'  => 'datetime', 'widget' => 'single_text', 'empty_data' => date("Y-m-d"),  "required" => true, 'disabled' => $disabled );
		$timedo = array( 'label' => 'Желаемое время отгрузки', 'widget' => 'single_text', 'empty_data' => date("H:i"),  "required" => true, 'disabled' => $disabled );
		$company = array( 'label' => 'Название компании', 'empty_data' => $order->getUser()->getCompany(), "required" => true, 'disabled' => $disabled );

		if( $deliverytypesRequest == '4' )
			$calculate = array( 'label' => 'Расчитать', 'empty_data' => 1, "required" => true, 'disabled' => $disabled );

		if( $deliverytypesRequest == '3' || $deliverytypesRequest == '4' )
			$location = array( 'label' => 'Город', 'empty_data' => $order->getUser()->getLocation(), "required" => true, 'disabled' => $disabled );

		if( $deliverytypesRequest == '2' || $deliverytypesRequest == '3' || $deliverytypesRequest == '4' ){
			$address = array( 'label' => 'Адрес', 'empty_data' => $order->getUser()->getAddress(), "required" => true, 'disabled' => $disabled );
			$full_name = array( 'label' => 'Контактное лицо', 'empty_data' => $order->getUser()->getFullname(), "required" => true, 'disabled' => $disabled );
			$telephone = array( 'label' => 'Телефон Контактного лица', 'empty_data' => $order->getUser()->getTelephone(),  "required" => true, 'disabled' => $disabled );
		}

		if( $order->getSupply() ){
			if( $order->getSupply()->getDeliverytype()->getId() != null )
				$deliverytypeint['empty_data'] = $order->getSupply()->getDeliverytype()->getId();

			if( $order->getSupply()->getDatedo()->format("Y-m-d") != null )
				$datedo['empty_data'] = $order->getSupply()->getDatedo()->format("Y-m-d");

			if( $order->getSupply()->getTimedo()->format("H:i") != null )
				$timedo['empty_data'] = $order->getSupply()->getTimedo()->format("H:i");

			if( $order->getSupply()->getCompany() != null )
				$company['empty_data'] = $order->getSupply()->getCompany();

			if( $deliverytypesRequest == '3' || $deliverytypesRequest == '4' )
				if( $order->getSupply()->getLocation() != null )
					$location['empty_data'] =  $order->getSupply()->getLocation();

			if( $deliverytypesRequest == '2' || $deliverytypesRequest == '3' || $deliverytypesRequest == '4' ){
				if( $order->getSupply()->getAddress() != null )
					$address['empty_data'] = $order->getSupply()->getAddress();
				if( $order->getSupply()->getFullname() != null )
					$full_name['empty_data'] = $order->getSupply()->getFullname();
				if( $order->getSupply()->getTelephone() != null )
					$telephone['empty_data'] = $order->getSupply()->getTelephone();
			}
		}


		$builder
			->add( 'deliverytypeint', 'choice', $deliverytypeint )
			->add( 'isdeliverytype', 'hidden' )
			->add( 'datedo', 'date', $datedo )
			->add( 'timedo', 'time', $timedo )
			->add( 'company', 'text', $company );

		if( $deliverytypesRequest == '4' )
			$builder->add( 'calculate', 'hidden', $calculate );

		if( $deliverytypesRequest == '3' || $deliverytypesRequest == '4' )
			$builder->add( 'location', 'text', $location );


		if( $deliverytypesRequest == '2' || $deliverytypesRequest == '3' || $deliverytypesRequest == '4' ){
			$builder
				->add( 'address', 'text', $address )
				->add( 'full_name', 'text', $full_name )
				->add( 'telephone', 'text', $telephone );
		}
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Top10\CabinetBundle\Entity\Supply'
        ));
    }

    public function getName()
    {
        return 'top10_cabinetbundle_supply';
    }
}
