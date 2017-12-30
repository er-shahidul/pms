<?php

namespace Pms\BudgetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class BudgetForSubCategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount','text',array(
                    'data'=> 0
                ))
            ->add('subCategory', 'entity_hidden', array(
                'class' => 'PmsSettingBundle:SubCategory',
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\BudgetBundle\Entity\BudgetForSubCategory'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'budgetforsubcategory';
    }
}
