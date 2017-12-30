<?php

namespace Pms\SettingBundle\Form\SearchForm;

use Pms\SettingBundle\Entity\Repository\AreaRepository;
use Pms\SettingBundle\Entity\Repository\ItemTypeRepository;
use Pms\SettingBundle\Entity\Repository\VendorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VendorSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendor','hidden')
            /*->add('vendor', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Vendor',
                'property' => 'vendorName',
                'required' => false,
                'empty_value' => 'Select Vendor Name',
                'empty_data' => null,
                'query_builder' => function (VendorRepository $repository)
                    {
                        return $repository->createQueryBuilder('v')
                            ->where('v.status = 1')
                            ->orderBy('v.vendorName','ASC');
                    }
            ))*/
            ->add('itemType', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:ItemType',
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
