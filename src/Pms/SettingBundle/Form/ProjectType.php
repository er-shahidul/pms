<?php

namespace Pms\SettingBundle\Form;

use Pms\SettingBundle\Entity\Repository\AreaRepository;
use Pms\SettingBundle\Entity\Repository\ProjectTypeRepository;
use Pms\UserBundle\Entity\Repository\UserRepository;
use Pms\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{

    public $isHeadOffice;
    public function __construct($isHeadOffice)
    {
            $this->isHeadOffice = $isHeadOffice;
    }
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projectName', 'text', array(
                'attr' => array(
                    'placeholder' => 'Add project name',
                    'autocomplete' => 'off'
                )
            ))
            ->add('costCenterNumber', 'text', array(
                'attr' => array(
                    'placeholder' => 'Cost Center Number',
                    'autocomplete' => 'off'
                )
            ))
            ->add('address', 'textarea')
            ->add('projectHead', 'entity', array(
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
            ->add('projectContactPerson', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select project Contact Person',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                    {
                        return $repository->createQueryBuilder('s')
                            ->where('s.enabled = 1');
                    }
            ))
            ->add('projectArea', 'entity', array(
                'class' => 'PmsSettingBundle:Area',
                'property' => 'areaName',
                'required' => false,
                'empty_value' => 'Select Area',
                'empty_data' => null,
                'query_builder' => function (AreaRepository $repository)
                    {
                        return $repository->createQueryBuilder('s')
                            ->where('s.status = 1');
                    }
            ))
            ->add('projectCategory', 'entity', array(
                'class' => 'PmsSettingBundle:ProjectType',
                'property' => 'projectCategoryName',
                'required' => false,
                'empty_value' => 'Select Type',
                'empty_data' => null,
                'query_builder' => function (ProjectTypeRepository $repository)
                    {
                        return $repository->createQueryBuilder('s')
                            ->where('s.status = 1');
                    }
            ))
            ->add('submit', 'submit');
        if(empty($this->isHeadOffice)) {
           $builder->add('isHeadOffice', 'checkbox');
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\SettingBundle\Entity\Project'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'project';
    }
}
