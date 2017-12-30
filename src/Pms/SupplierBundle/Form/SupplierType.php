<?php

namespace Pms\SupplierBundle\Form;

use Pms\SettingBundle\Entity\ItemType;
use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\SettingBundle\Entity\Repository\ItemRepository;
use Pms\SettingBundle\Entity\Repository\ItemTypeRepository;
use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SupplierType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('item')
            ->add('contactPerson')
            ->add('contactNo')
            ->add('specification')
            ->add('brand')
            ->add('itemType', 'entity', array(
                'class' => 'PmsSettingBundle:itemType',
                'property' => 'itemType',
                'required' => false,
                'empty_value' => 'Select Item Type',
                'empty_data' => null,
                'query_builder' => function (ItemTypeRepository $repository)
                {
                    return $repository->createQueryBuilder('it')
                        ->where('it.status = 1')
                        ->orderBy('it.itemType','ASC');
                }
            ))
            ->add('category', 'entity', array(
                'class' => 'PmsSettingBundle:Category',
                'property' => 'categoryName',
                'required' => false,
                'empty_value' => 'Select Category',
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
            ->add('email')
            ->add('remark')
            ->add('files','file',array(
                'multiple'=>true
            ))
            ->add('save','submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\SupplierBundle\Entity\Supplier'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_supplierbundle_supplier';
    }
}
