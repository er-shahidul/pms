<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\AreaRepository;
use Pms\SettingBundle\Entity\Repository\ItemTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompareLowestPriceSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemType', 'entity', array(
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
            ->add('start_date', 'text',array(
                'attr' => array(
                    'placeholder' => 'Start Date'
                ),
                'read_only' => true
            ))
            ->add('end_date', 'text',array(
                'attr' => array(
                    'placeholder' => 'End Date'
                ),
                'read_only' => true
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
