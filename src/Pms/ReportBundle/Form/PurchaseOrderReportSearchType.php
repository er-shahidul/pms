<?php

namespace Pms\ReportBundle\Form;

use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\SettingBundle\Entity\Repository\PurchaseTypeRepository;
use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PurchaseOrderReportSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project', 'entity', array(
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
            ))
            ->add('user', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select User',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->where('u.enabled = 1')
                        ->orderBy('u.username','ASC');
                }
            ))
            ->add('purchaseType', 'entity', array(
                'class' => 'PmsSettingBundle:PurchaseType',
                'property' => 'name',
                'required' => false,
                'empty_value' => 'Select PurchaseType',
                'empty_data' => null,
                'query_builder' => function (PurchaseTypeRepository $repository)
                {
                    return $repository->createQueryBuilder('pt')
                        ->where('pt.status = 1')
                        ->orderBy('pt.id','ASC');
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
