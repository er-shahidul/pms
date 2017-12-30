<?php

namespace Pms\ReportBundle\Form;

use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PurchaseOfficerSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select User',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->join('u.groups', 'g')
                        ->where("g.roles LIKE '%ROLE_BUYER_USERS%'")
                        ->andWhere('u.enabled = 1')
                        ->orderBy('u.username', 'ASC');
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
