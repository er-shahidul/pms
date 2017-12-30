<?php

namespace Pms\SettingBundle\Form;

use Pms\SettingBundle\Entity\Repository\SubCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CostHeaderType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('subCategory', 'entity', array(
                'class' => 'PmsSettingBundle:SubCategory',
                'property' => 'SubCategoryName',
                'required' => false,
                'empty_value' => 'Select SubCategory',
                'empty_data' => null,
                'query_builder' => function (SubCategoryRepository $repository)
                    {
                        return $repository->createQueryBuilder('s')
                            ->where('s.status = 1');
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
            'data_class' => 'Pms\SettingBundle\Entity\CostHeader'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'costheader';
    }
}
