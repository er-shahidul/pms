<?php

namespace Pms\CoreBundle\Form;

use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\SettingBundle\Entity\Repository\CostHeaderRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PurchaseRequisitionType extends AbstractType
{
    private $user;
    private $role;

    public function __construct($user, $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('refNo', 'text',array(
                'attr' => array(
                    'autocomplete' => 'off'
                )
            ))
            ->add('prCost', 'text',array(
                'attr' => array(
                    'autocomplete' => 'off'
                ),
                'read_only' => true
            ))
            ->add('file', 'file')
            ->add('priority', 'choice', array(
                'choices' => array(
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High'
                )
            ))
            ->add('project', 'entity', array(
                'class' => 'PmsSettingBundle:Project',
                'property' => 'projectName',
                'required' => false,
                'empty_value' => 'Select Project',
                'empty_data' => null,
                'query_builder' => function (ProjectRepository $repository)
                {
                    if(in_array('ROLE_SUPER_ADMIN', $this->role)) {
                        return $repository->createQueryBuilder('p')
                            ->where('p.status = 1')
                            ->orderBy('p.projectName', 'ASC');
                    }else{
                        return $repository->createQueryBuilder('p')
                            ->join('p.users', 'u')
                            ->where('p.status = 1')
                            ->andWhere('u IN(:user)')
                            ->setParameter('user', $this->user)
                            ->orderBy('p.projectName', 'ASC');
                    }
                }
            ))
            ->add('costHeader', 'entity', array(
                'class' => 'PmsSettingBundle:CostHeader',
                'property' => 'title',
                'required' => false,
                'empty_value' => 'Select Cost Header',
                'empty_data' => null,
                'query_builder' => function (CostHeaderRepository $repository)
                    {
                        return $repository->createQueryBuilder('ch')
                            ->where('ch.status = 1');
                    }
            ))
            ->add('category', 'entity', array(
                'class' => 'PmsSettingBundle:Category',
                'property' => 'categoryName',
                'required' => false,
                'empty_data' => null,
                'query_builder' => function (CategoryRepository $repository)
                    {
                        return $repository->createQueryBuilder('c')
                            ->where('c.status = 1');
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
            ->add('purchaseRequisitionItems', 'collection', array(
                'type' => new PurchaseRequisitionItemType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype' => true,
                'label_attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('submit', 'submit');
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\PurchaseRequisition'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'purchaserequisition';
    }
}
