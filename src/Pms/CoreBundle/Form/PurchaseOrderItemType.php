<?php

namespace Pms\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class PurchaseOrderItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', 'text',array(
                    'constraints' => array(
                        new NotBlank(
                            array('message'=>'Quantity is required')
                        ),
                    )
            ))
            ->add('price', 'text',array(
                    'constraints' => array(
                        new NotBlank(
                            array('message'=>'Price is required')
                        ),
                    )
            ))
            ->add('amount', 'text', array(
                'attr' => array(
                    'placeholder' => 'Line Total'
                ),
                'read_only' => true
            ))
            ->add('brand', 'text', array(
                'attr' => array(
                    'placeholder' => 'brand'
                )
            ))
            ->add('purchaseRequisitionItem', 'entity_hidden', array(
                "class" => "PmsCoreBundle:PurchaseRequisitionItem",
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\PurchaseOrderItem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'purchaseorderitem';
    }
}
