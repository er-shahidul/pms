<?php

namespace Pms\SettingBundle\Form\SearchForm;

use Pms\SettingBundle\Entity\Repository\AreaRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\SettingBundle\Entity\Repository\ProjectTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectSearchType extends AbstractType
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
            ->add('projectType', 'entity', array(
                'class' => 'PmsSettingBundle:ProjectType',
                'property' => 'projectCategoryName',
                'required' => false,
                'empty_value' => 'Select Company Type',
                'empty_data' => null,
                'query_builder' => function (ProjectTypeRepository $repository)
                {
                    return $repository->createQueryBuilder('pt')
                        ->where('pt.status = 1')
                        ->orderBy('pt.projectCategoryName','ASC');
                }
            ))
            ->add('area', 'entity', array(
                'class' => 'PmsSettingBundle:Area',
                'property' => 'areaName',
                'required' => false,
                'empty_value' => 'Select Area',
                'empty_data' => null,
                'query_builder' => function (AreaRepository $repository)
                {
                    return $repository->createQueryBuilder('at')
                        ->where('at.status = 1')
                        ->orderBy('at.areaName','ASC');
                }
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
