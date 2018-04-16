<?php

namespace Top10\CabinetBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // add your custom field
        $builder
        ->add('company', null, array('label' => 'Фактическое название Компании *', "required" => true))
        ->add('companyYur', null, array('label' => 'Юр. название Компании *', "required" => true))
		->add('location', null, array('label' => 'Город *', "required" => true))
		->add('address', null, array('label' => 'Фактический адрес *', "required" => true))
        ->add('inn', null, array('label' => 'ИНН *', "required" => true))
        ->add('scope', null, array('label' => 'Сфера деятельности (опт, розница, интернет, другое) *', "required" => true))
        ->add('shop', null, array('label' => 'Кол-во магазинов *', "required" => true))
        ->add('telephone', null, array('label' => 'Телефон *', "required" => true))
        ->add('message', null, array('label' => 'Сообщение', "required" => false))
        ->add('plainPassword', 'hidden', array('data' => 'abcdef'))
        ->add('new', 'hidden', array('data' => true))
        
        ;
        
    }

    public function getName()
    {
    	return 'top10_user_registration';
    }
    
}