<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DailyRequisitionSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('subCategory', 'entity', array(
                'class' => 'PmsSettingBundle:SubCategory',
                'property' => 'subCategoryName',
                'required' => false,
                'empty_value' => 'Select Sub Category',
                'empty_data' => null,
                'query_builder' => function (SubCategoryRepository $repository)
                {
                    return $repository->createQueryBuilder('sc')
                        ->join('sc.category', 'ca')
                        ->where('sc.status = 1')
                        ->andWhere('ca.id IS NOT NULL')
                        ->orderBy('sc.subCategoryName', 'ASC');
                }
            ))
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
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'requisition_report_search_daily';
    }
}
