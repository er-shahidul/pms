<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\SettingBundle\Entity\Repository\CostHeaderRepository;
use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountSearchType extends AbstractType
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
            ))
            ->add('category', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Category',
                'property' => 'categoryName',
                'required' => false,
                'empty_value' => 'Select Category',
                'empty_data' => null,
                'query_builder' => function (CategoryRepository $repository)
                {
                    return $repository->createQueryBuilder('i')
                        ->where('i.status = 1')
                        ->orderBy('i.categoryName','ASC');
                }
            ))
            ->add('subCategory', 'entity', array(
                    'attr' => array(
                        'class' => 'fontColorBlack'
                    ),
                    'class' => 'PmsSettingBundle:SubCategory',
                    'property' => 'subCategoryName',
                    'required' => false,
                    'empty_value' => 'Select Sub Category',
                    'empty_data' => null,
                    'query_builder' => function (SubCategoryRepository $repository)
                        {
                            return $repository->createQueryBuilder('i')
                                ->where('i.status = 1')
                                ->orderBy('i.subCategoryName','ASC');
                        }
            ))
            ->add('costHeader', 'entity', array(
                    'attr' => array(
                        'class' => 'fontColorBlack'
                    ),
                    'class' => 'PmsSettingBundle:CostHeader',
                    'property' => 'title',
                    'required' => false,
                    'empty_value' => 'Select Cost Header',
                    'empty_data' => null,
                    'query_builder' => function (CostHeaderRepository $repository)
                        {
                            return $repository->createQueryBuilder('i')
                                ->where('i.status = 1')
                                ->orderBy('i.title','ASC');
                        }
            ))
            ->add('start_date', 'text',array(
                    'attr' => array(
                        'placeholder' => 'Date'
                    ),
                    'data' => '0'
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
        return 'account_search';
    }
}
