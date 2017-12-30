<?php

namespace Pms\DocumentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('purchaseOrder','hidden')
            ->add('description', 'textarea', array(
                'attr' => array(
                    'placeholder' => 'Remark'
                )
            ))
            ->add('billNumber', 'text', array(
                'attr' => array(
                    'placeholder' => 'Bill Number'
                )
            ))
            ->add($builder->create('dateOfBill', 'text', array(
                'attr' => array(
                    'placeholder' => 'Date'
                ),
                'data_class' => null,
                'read_only' => true
            ))->addViewTransformer(new DateTimeToStringTransformer(null, null, 'Y-m-d')))
            ->add('billAmount', 'text', array(
                'attr' => array(
                    'placeholder' => 'Bill Amount'
                )
            ))
            ->add('invoiceOrCalan', 'choice', array(
                'empty_value' => 'Select one',
                'choices' => array(
                    '1' => 'invoice',
                    '0' => 'challan',
                )
            ))
            ->add('file', 'file')
            ->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\DocumentBundle\Entity\Document'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'document';
    }
}
