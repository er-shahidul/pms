<?php

namespace Pms\BudgetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BudgetType extends AbstractType
{
    private $project;
    private $user;

    public function __construct($project, $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file')
            ->add($builder->create('month', 'text', array(
                'attr' => array(
                    'placeholder' => 'Select Month'
                ),
                'data_class' => null,
                'read_only' => true
            ))->addViewTransformer(new DateTimeToStringTransformer(null, null, 'd-m-Y')))
            ->add('budgetForSubCategories', 'collection', array(
                'type' => new BudgetForSubCategoryType(),
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
                'data' => '0',
                'read_only' => true
            ))
            ->add('project', 'hidden', array(
                'data' =>$this->project
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
