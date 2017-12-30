<?php

namespace Pms\CoreBundle\Form;

use Pms\DocumentBundle\Entity\Repository\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReceiveType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file')
            ->add('receiveItems', 'collection', array(
                'type' => new ReceivedItemType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype' => true,
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('buyer', 'text', array(
                'required' => false,
                'empty_data' => null,
                'read_only' => true
            ))
            ->add('vendor', 'text', array(
                'required' => false,
                'empty_data' => null,
                'read_only' => true
            ))
            ->add('refGrn', 'text', array(
                'attr' => array(
                    'autocomplete' => 'off'
                )
            ))
            ->add('contactNumber', 'text', array(
                'attr' => array(
                    'placeholder' => 'Contact Number',
                    'autocomplete' => 'off'
                )
            ))
            ->add('invoice', 'hidden_entity',array(
                'class'=>'Pms\DocumentBundle\Entity\Document',
                'attr'=>array(
                    'placeholder' => 'Select Invoice',
                )
            ))
            ->add('calan', 'hidden_entity',array(
                'class'=>'Pms\DocumentBundle\Entity\Document',
                'attr'=>array(
                    'placeholder' => 'Select Chalan',
                )
            ))
            /*->add('invoice', 'entity', array(
                'class' => 'PmsDocumentBundle:Document',
                'property' => 'formattedTitle',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Select Invoice'
                ),
                'empty_data' => null,
                'query_builder' => function (DocumentRepository $repository)
                    {
                        return $repository->createQueryBuilder('d')
                            ->where('d.invoiceOrCalan = 1')
                            ->andWhere('d.billAmount is not null')
                            ->andWhere('d.billNumber is not null')
                            ->andWhere('d.dateOfBill is not null');;
                    }
            ))*/
            /*->add('calan', 'entity', array(
                'class' => 'PmsDocumentBundle:Document',
                'property' => 'formattedTitle',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Select Calan'
                ),
                'empty_data' => null,
                'query_builder' => function (DocumentRepository $repository)
                    {
                        return $repository->createQueryBuilder('d')
                            ->where('d.invoiceOrCalan = 0')
                            ->andWhere('d.billAmount is not null')
                            ->andWhere('d.billNumber is not null')
                            ->andWhere('d.dateOfBill is not null');
                    }
            ))*/
            ->add('receiveForm', 'text', array(
                'mapped' => false,
                'read_only' => true
            ))
            ->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\Receive'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'receive';
    }
}
