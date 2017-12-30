<?php

namespace Pms\SettingBundle\Form;

use Pms\SettingBundle\Entity\Repository\CategoryRepository;
use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubCategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                    }
            ))
            ->add('subCategoryName', 'text', array(
                'label_attr' => array(
                    'class' => 'hidden'
                ),
                'attr' => array(
                    'autocomplete' => 'off'
                )
            ))
              ->add('head', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select Head',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('s')
                        ->where('s.enabled = 1');
                }
            ))
            ->add('subHead', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select Sub Head',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('s')
                        ->where('s.enabled = 1');
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
            'data_class' => 'Pms\SettingBundle\Entity\SubCategory'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'subcategory';
    }
}
