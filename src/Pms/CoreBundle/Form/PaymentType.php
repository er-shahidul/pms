<?php

namespace Pms\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('requestAmount','hidden')
            ->add('billNumber','text')
            ->add('paymentDate','text')
            ->add('cheque','text')
            ->add('relatedBank', 'entity', array(
                'class' => 'PmsSettingBundle:RelatedBank',
                'property' => 'name',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Select Bank'
                ),
                'empty_data' => null,
            ));

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\Payment'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_corebundle_payment';
    }
}
