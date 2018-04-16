<?php

namespace Top10\CabinetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Collection;
use Top10\CabinetBundle\Form\Model\CatalogFilter;

class OrderFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date_from', 'date', array(
            'widget' => 'single_text',
            'input'  => 'datetime',
            'format' => 'dd.MM.yyyy'
        ));
        $builder->add('date_to', 'date', array(
            'widget' => 'single_text',
            'input'  => 'datetime',
            'format' => 'dd.MM.yyyy'
        ));
    }

    public function getName()
    {
        return 'o';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'                => 'Top10\CabinetBundle\Form\Model\OrderFilter',
            'required'                  => false,
            'translation_domain'        => 'form',
            'csrf_protection'           => false,
        ));
    }
}
