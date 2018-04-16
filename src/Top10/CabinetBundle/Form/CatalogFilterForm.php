<?php

namespace Top10\CabinetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Collection;
use Top10\CabinetBundle\Form\Model\CatalogFilter;

class CatalogFilterForm extends AbstractType
{
    protected $opts = array();

    public function __construct(array $options)
    {
        if( !isset($options['empty_form']) ) {
            $options['empty_form'] = true;
        }

        $this->opts = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $opts = $this->opts;

        /** @var $catalogFilter CatalogFilter */
        $catalogFilter = $builder->getData();
        $filters = $catalogFilter->getFilters();
        $catalogType = $catalogFilter->getType();

        $builder
            ->add('price_from', 'number')
            ->add('price_to', 'number')
        ;

        // общие параметры
        $paramList = $catalogFilter->getSharedParamList();
        // индивидуальные параметры
        if( $catalogType ) {
            $paramList = array_merge($paramList, $catalogFilter->getParamList( $catalogType ));
        }
        foreach($paramList as $paramName) {
            if( in_array($paramName, array('price_from', 'price_to')) ) {
                continue;
            }
            else {
                if( $opts['empty_form'] ) {
                    $builder->add($paramName, 'text');
                }
                else {
					$builder->add($paramName, 'choice', array(
                        'choices' => $filters[$paramName],
                        'required' => false,
                        'empty_value' => 'Любой',
                        'empty_data' => null
                    ));
                }
            }
        }

        // спрятанные
        if( $catalogType ) {
            $hideParamList = $catalogFilter->getParamList( $catalogType == 'tire' ? 'disk' : 'tire' );
        }
        else {
            $hideParamList = $catalogFilter->getParamList( 'tire' );
            $hideParamList = array_merge($hideParamList, $catalogFilter->getParamList( 'disk' ));
        }

        foreach($hideParamList as $paramName) {
            if( $opts['empty_form'] ) {
                $builder->add($paramName, 'text');
            }
            else {
                $builder->add($paramName, 'choice', array(
                    'choices' => array(),
                    'required' => false,
                    'empty_value' => 'Любой',
                    'empty_data' => null
                ));
            }
        }

    }

    public function getName()
    {
        return 'f';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'                => 'Top10\CabinetBundle\Form\Model\CatalogFilter',
            'required'                  => false,
            'translation_domain'        => 'form',
            'csrf_protection'           => false,
        ));
    }
}
