<?php

namespace Pms\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReceivedItemType extends AbstractType
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
            ->add('purchaseOrderItem', 'entity_hidden', array(
                "class" => "PmsCoreBundle:purchaseOrderItem",
            ))
            ->add('comment', 'textarea')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\ReceivedItem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'receiveditem';
    }
}
