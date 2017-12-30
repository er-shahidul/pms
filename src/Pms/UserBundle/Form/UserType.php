<?php

namespace Pms\UserBundle\Form;

use Doctrine\ORM\EntityRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Pms\UserBundle\Form\Type\ProfileFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class UserType extends BaseType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

	    $builder
		    ->add('fullName');
        $builder
            ->add('projects', 'entity', array(
                'class' => 'PmsSettingBundle:Project',
                'property' => 'projectName',
                'required' => false,
                'empty_value' => 'Select Project',
                'empty_data' => null,
                'query_builder' => function (ProjectRepository $repository)
                    {
                        return $repository->createQueryBuilder('p')
                            ->where('p.status = 1');
                    },
                'multiple' => true,
            ));
	    $builder
		    ->add('groups', 'entity', array(
			    'class' => 'Pms\UserBundle\Entity\Group',
			    'query_builder' => function(EntityRepository $er) {
				    return $er->createQueryBuilder('g')
				              ->andWhere("g.name != :group")
				              ->setParameter('group', 'Super Administrator');
			    },
			    'property' => 'name',
			    'multiple' => true,
		    ));
	    $builder->add('profile', new ProfileFormType());
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
}