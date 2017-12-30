<?php

namespace Pms\BudgetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BudgetEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file')
            ->add('budgetForSubCategories', 'collection', array(
                'type' => new BudgetForSubCategoryEditType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype' => true,
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('netTotal', 'text', array(
                'attr' => array(
                    'placeholder' => 'Net Total'
                ),
                'read_only' => true
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
            'data_class' => 'Pms\BudgetBundle\Entity\Budget'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'budget';
    }
}
