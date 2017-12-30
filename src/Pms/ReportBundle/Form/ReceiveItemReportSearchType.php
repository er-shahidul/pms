<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\SettingBundle\Entity\Repository\VendorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReceiveItemReportSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('po', 'hidden')
            ->add('project', 'entity', array(
                'class' => 'PmsSettingBundle:Project',
                'property' => 'projectName',
                'required' => false,
                'empty_value' => 'Select Project',
                'empty_data' => null,
                'query_builder' => function (ProjectRepository $repository)
                {
                    return $repository->createQueryBuilder('p')
                        ->where('p.status = 1')
                        ->orderBy('p.projectName','ASC');
                }
            ))
            ->add('vendor', 'entity', array(
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
            ))
           /* ->add('start_date', 'text',array(
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
            ))*/
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
