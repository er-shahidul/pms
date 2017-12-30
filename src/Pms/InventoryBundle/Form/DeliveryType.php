<?php

namespace Pms\InventoryBundle\Form;

use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\SettingBundle\Entity\Repository\CostHeaderRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeliveryType extends AbstractType
{

    private $projectId;
    private $user;
    private $status;

    public function __construct($project, $user,$status)
    {
        $this->projectId = $project;
        $this->user = $user;
        $this->status = $status;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('getPass', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'attr' => array(
                    'placeholder' => 'Get Pass'
                )
            ))
           ->add('project', 'hidden',array(
               'data' =>$this->projectId
           ))
            ->add('file','file')
            ->add('fileTwo','file')
            ->add('deliveryItem', 'collection', array(
                'type' => new DeliveryItemType($this->projectId),
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype' => true,
                'label_attr' => array(
                    'class' => 'hidden'
                )
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
                        ->where('ch.status = 1')
                        ->orderBy('ch.title');
                }
            ));

            if($this->status) {

                $builder->add('issuedToCustomer', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select User',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->join('u.projects', 'p')
                        ->join('u.groups', 'g')
                        ->where("g.roles LIKE '%ROLE_RECEIVE_ADD%'")
                        ->groupBy('u.id')
                        ->orderBy('u.username', 'ASC');
                }
              ))
                    ->add('issuedToProject', 'entity', array(
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
                    ));
            }else{
                $builder->add('customerId', 'entity', array(
                    'class' => 'UserBundle:User',
                    'property' => 'username',
                    'required' => false,
                    'empty_value' => 'Select User',
                    'empty_data' => null,
                    'query_builder' => function (UserRepository $repository)
                    {
                        return $repository->createQueryBuilder('u')
                            ->join('u.projects', 'p')
                            ->where('p IN(:project)')
                            ->setParameter('project', $this->projectId)
                            ->orderBy('u.username', 'ASC');
                    }
                ));
            }

        $builder->add('category', 'entity', array(
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
            ->add('submit', 'submit');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\InventoryBundle\Entity\Delivery'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_inventorybundle_delivery';
    }
}
