<?php

namespace Pms\UserBundle\Form\SearchForm;

use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserSearchType extends AbstractType
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
                        ->orderBy('u.username','ASC');
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
