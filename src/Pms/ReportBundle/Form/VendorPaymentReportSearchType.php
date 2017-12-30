<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\VendorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VendorPaymentReportSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendor','hidden')
            /*->add('vendor', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Vendor',
                'property' => 'vendorName',
                'required' => false,
                'empty_value' => 'Select Vendor Name',
                'empty_data' => null,
                'query_builder' => function (VendorRepository $repository)
                {
                    return $repository->createQueryBuilder('v')
                        ->where('v.status = 1')
                        ->orderBy('v.vendorName','ASC');
                }
            ))*/
            ->add('start_date', 'text',array(
                'attr' => array(
                    'placeholder' => 'Start Date'
                ),
                'read_only' => true
            ))
            ->add('end_date', 'text',array(
                'attr' => array(
                    'placeholder' => 'End Date'
                ),
                'read_only' => true
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(

        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'search';
    }
}
