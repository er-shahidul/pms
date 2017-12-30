<?php

namespace Pms\SettingBundle\Form;

use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\SettingBundle\Entity\Repository\ItemTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemName', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add item name',
                    'autocomplete' => 'off'
                )
            ))
            ->add('price', 'text', array(
                'attr' => array(
                    'placeholder' => 'Price',
                    'autocomplete' => 'off'
                )
            ))
            ->add('itemUnit', 'text', array(
                'attr' => array(
                    'placeholder' => 'Unit of measurement',
                    'autocomplete' => 'off'
                )
            ))
            ->add('itemType', 'entity', array(
                'class' => 'PmsSettingBundle:ItemType',
                'property' => 'itemType',
                'required' => false,
                'empty_value' => 'Select Type',
                'empty_data' => null,
                'query_builder' => function (ItemTypeRepository $repository)
                    {
                        return $repository->createQueryBuilder('it')
                            ->where('it.status = 1');
                    }
            ))
            ->add('submit', 'submit');
        $builder
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
                    },
                'multiple' => true,
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\SettingBundle\Entity\Item'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'item';
    }
}
