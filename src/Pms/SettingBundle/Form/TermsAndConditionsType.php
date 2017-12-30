<?php

namespace Pms\SettingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TermsAndConditionsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('condition','text')
            ->add('sector', 'entity', array(
                'class' => 'PmsSettingBundle:PurchaseType',
                'property' => 'name',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Select Purchase Type'
                ),
                'empty_data' => null,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\SettingBundle\Entity\TermsAndConditions'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'termsandconditions';
    }
}
