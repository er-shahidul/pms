<?php

namespace Pms\CoreBundle\Form;

use Pms\SettingBundle\Entity\Repository\PurchaseTypeRepository;
use Pms\SettingBundle\Entity\Repository\VendorRepository;
use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PurchaseOrderAmendmentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('refNo', 'text',array(
                'attr' => array(
                    'autocomplete' => 'off'
                )
            ))
            ->add('tax', 'text')
            ->add('paymentType', 'choice', array(
                'empty_value' => 'Select payment type',
                'choices' => array(
                    'After delivery payment' => 'After delivery payment',
                    'Advance payment' => 'Advance payment',
                    'Cash payment' => 'Cash payment'
                )
            ))
            ->add('paymentFrom', 'text',array(
                'read_only'=>true
            ))
            ->add('customTermCondition', 'textarea')
            ->add('computationStatus', 'choice', array(
                'empty_value' => 'Select one',
                'choices' => array(
                    1 => 'Yes',
                    2 => 'No'
                )
            ))
            ->add('vendorQuotationReferenceNumber', 'text')
            ->add('file', 'file')
            ->add('fileTwo', 'file')
            ->add('fileAmendment', 'file')
            ->add('netTotal', 'text', array(
                'attr' => array(
                    'placeholder' => 'Net Total'
                ),
                'read_only' => true
            ))
            ->add('subTotal', 'text', array(
                'attr' => array(
                    'placeholder' => 'Sub Total'
                ),
                'read_only' => true
            ))
            ->add('poNonpo', 'entity', array(
                'class' => 'PmsSettingBundle:PurchaseType',
                'property' => 'name',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Select Purchase Type'
                ),
                'empty_data' => null,
                'query_builder' => function (PurchaseTypeRepository $repository)
                {
                    return $repository->createQueryBuilder('pt')
                        ->andWhere("pt.status = 1")
                        ->orderBy('pt.name','ASC');
                }

            ))
            ->add('paymentMethod', 'choice', array(
                'empty_value' => 'Select payment method',
                'choices' => array(
                    'Cash' => 'Cash',
                    'Cheque' => 'Cheque',
                )
            ))
            ->add('comment', 'textarea')
            ->add('amendmentComment', 'textarea')
            ->add($builder->create('dateOfDelivered', 'text', array(
                'attr' => array(
                    'placeholder' => 'Date'
                ),
                'data_class' => null,
                'read_only' => true
            ))->addViewTransformer(new DateTimeToStringTransformer(null, null, 'Y-m-d')))
            ->add('purchaseOrderItems', 'collection', array(
                'type' => new PurchaseOrderItemType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype' => true,
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('buyer', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'attr' => array(
                    'placeholder' => ' Select Buyer'
                ),
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->join('u.groups', 'g')
                        ->where("g.roles LIKE '%ROLE_BUYER_USERS%'")
                        ->andWhere('u.enabled = 1')
                        ->orderBy('u.username', 'ASC');
                }
            ))
            ->add('vendor', 'entity', array(
                'class' => 'PmsSettingBundle:Vendor',
                'property' => 'vendorName',
                'required' => false,
                'attr' => array(
                    'placeholder' => ' Select Vendor'
                ),
                'empty_data' => null,
                'query_builder' => function (VendorRepository $repository)
                {
                    return $repository->createQueryBuilder('v')
                        ->where('v.status = 1')
                        ->orderBy('v.vendorName', 'ASC');
                }
            ))
            ->add('advancePaymentAmount', 'text')
            ->add('submit', 'submit');
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\PurchaseOrder'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'purchaseorder';
    }
}
