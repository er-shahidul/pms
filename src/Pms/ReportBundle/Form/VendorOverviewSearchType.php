<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Area;
use Pms\SettingBundle\Entity\Repository\AreaRepository;
use Pms\SettingBundle\Entity\Repository\VendorRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VendorOverviewSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('area', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Area',
                'property' => 'areaName',
                'required' => false,
                'empty_value' => 'Select Area',
                'empty_data' => null,
                'query_builder' => function (AreaRepository $repository)
                    {
                        return $repository->createQueryBuilder('a')
                            ->where('a.status = 1')
                            ->orderBy('a.areaName','ASC');
                    }
            ))
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
            ->add('month', 'text',array(
                'attr' => array(
                    'placeholder' => 'Select Month'
                ),
                'read_only' => true
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
