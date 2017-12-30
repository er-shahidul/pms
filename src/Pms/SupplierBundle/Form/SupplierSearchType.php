<?php

namespace Pms\SupplierBundle\Form;

use Pms\SettingBundle\Entity\Repository\ItemTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SupplierSearchType extends AbstractType
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
            ->add('month', 'text',array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Select month'
                ),
            )) ;
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
        return 'search_supplier';
    }
}
