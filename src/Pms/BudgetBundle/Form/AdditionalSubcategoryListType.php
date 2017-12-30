<?php

namespace Pms\BudgetBundle\Form;

use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Pms\SettingBundle\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdditionalSubcategoryListType extends AbstractType
{

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount','text')
            ->add('subcategory', 'entity', array(
                'class' => 'PmsSettingBundle:SubCategory',
                'property' => 'subCategoryName',
                'required' => false,
                'empty_value' => 'Select Subcategory',
                'empty_data' => null,
                'query_builder' => function (SubCategoryRepository $repository)
                {
                    return $repository->createQueryBuilder('s')
                        ->orderBy('s.subCategoryName','ASC');
                }
            ))
        ->add('save','submit');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\BudgetBundle\Entity\AdditionalBudgetForSubCategory'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'budgetsubcategorylist';
    }
}
