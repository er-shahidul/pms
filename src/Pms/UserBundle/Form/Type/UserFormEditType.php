<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pms\UserBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserFormEditType extends AbstractType
{

    private $newEntry;

    /**
     * @param bool   $newEntry
     */
    public function __construct($newEntry = true)
    {
        $this->newEntry = $newEntry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName');

        if($this->newEntry){
            $builder
                ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
             ;
        }

        $builder
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('groups', 'entity', array(
                'class' => 'Pms\UserBundle\Entity\Group',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('g')
                            ->andWhere("g.name != :group")
                            ->setParameter('group', 'Super Administrator');
                    },
                'property' => 'name',
                'multiple' => true,
                'label_attr' => array(
                    'class' => 'hidden'
                ),
                'attr' => array(
                    'class' => 'hidden'
                )
            ))
            ->add('profile', new ProfileFormType());
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
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\UserBundle\Entity\User',
            'validation_groups'  => 'registration',
        ));
    }

    public function getName()
    {
        return 'manage_user';
    }
}
