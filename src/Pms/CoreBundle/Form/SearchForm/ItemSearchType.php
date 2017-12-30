<?php

namespace Pms\CoreBundle\Form\SearchForm;

use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\SettingBundle\Entity\Repository\ItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Item',
                'property' => 'itemName',
                'required' => false,
                'empty_value' => 'Select Item',
                'empty_data' => null,
                'query_builder' => function (ItemRepository $repository)
                    {
                        return $repository->createQueryBuilder('i')
                            ->where('i.status = 1')
                            ->orderBy('i.itemName','ASC');
                    }
            ))
            ->add('category', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Category',
                'property' => 'categoryName',
                'required' => false,
                'empty_value' => 'Select Category',
                'empty_data' => null,
                'query_builder' => function (CategoryRepository $repository)
                {
                    return $repository->createQueryBuilder('i')
                        ->where('i.status = 1')
                        ->orderBy('i.categoryName','ASC');
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
        return 'item_search';
    }
}
