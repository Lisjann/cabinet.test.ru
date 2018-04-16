<?php

namespace Top10\CabinetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('data')
            ->add('numberdoc')
            ->add('description')
            ->add('price')
            ->add('delay')
            ->add('debt')
            ->add('overdue')
            ->add('duty')
            ->add('fines')
            ->add('created')
            ->add('updated')
            ->add('user')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Top10\CabinetBundle\Entity\Payment'
        ));
    }

    public function getName()
    {
        return 'top10_cabinetbundle_paymenttype';
    }
}
