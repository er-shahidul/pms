<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\ProjectTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentCompanyReportSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('companyType', 'entity', array(
                'class' => 'PmsSettingBundle:ProjectType',
                'property' => 'projectCategoryName',
                'required' => false,
                'empty_value' => 'Select All',
                'empty_data' => null,
                'query_builder' => function (ProjectTypeRepository $repository)
                {
                    return $repository->createQueryBuilder('pt')
                        ->where('pt.status= 1')
                        ->orderBy('pt.projectCategoryName','ASC');
                }
            ))
            ->add('year', 'text',array(
                'attr' => array(
                    'placeholder' => 'Select Year'
                ),
                'data' => '0'
            ));
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
