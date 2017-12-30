<?php

namespace Pms\SettingBundle\Form;

use Pms\SettingBundle\Entity\Repository\ItemTypeRepository;
use Pms\SettingBundle\Entity\Vendor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class VendorType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendorName', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add vendor name',
                    'autocomplete' => 'off'
                )
            ))
            ->add('contractPerson', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Contract Person',
                    'autocomplete' => 'off'
                )
            ))
            ->add('contractNo', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Contract No',
                    'autocomplete' => 'off'
                )
            ))
            ->add('email', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Email',
                    'autocomplete' => 'off'
                )
            ))
            ->add('tradeLicenseNo', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Trade License No',
                    'autocomplete' => 'off'
                )
            ))
            ->add('tinCertificateNo', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Tin Certificate No',
                    'autocomplete' => 'off'
                )
            ))
            ->add('vatCertificateNo', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add VAT Certificate No',
                    'autocomplete' => 'off'
                )
            ))
            ->add('bankAccountNo', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Account No',
                    'autocomplete' => 'off'
                )
            ))
            ->add('bankAccountName', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Bank Account Name',
                    'autocomplete' => 'off'
                )
            ))
            ->add('branchName', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add Branch Name',
                    'autocomplete' => 'off'
                )
            ))
            ->add('vendorAddress', 'textarea', array(
                'attr' => array(
                    'placeholder' => 'Add Address',
                    'autocomplete' => 'off'
                )
            ))
            ->add('area', 'entity', array(
                'class' => 'PmsSettingBundle:Area',
                'property' => 'areaName',
                'required' => true,
                'empty_value' => 'Select Area',
                'empty_data' => null
            ))
            ->add('paymentType', 'choice', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'choices' => array(
                    'cheque' => 'Cheque',
                    'cash' => 'Cash'
                ),
                'empty_value' => 'Select Payment Type',
            ))
            ->add('itemTypes', 'entity', array(
                'class' => 'PmsSettingBundle:ItemType',
                'property' => 'itemType',
                'required' => false,
                'empty_value' => 'Select Item Type',
                'empty_data' => null,
                'query_builder' => function (ItemTypeRepository $repository)
                {
                    return $repository->createQueryBuilder('it')
                        ->where('it.status = 1');
                },
                'multiple' => true,
            ))
            ->add('vat_file', new VendorAttachType(Vendor::VAT),array(
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('tax_file', new VendorAttachType(Vendor::TAX),array(
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('trade_file', new VendorAttachType(Vendor::TRADE),array(
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('submit', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\SettingBundle\Entity\Vendor'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'vendor';
    }
}
