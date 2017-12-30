<?php

namespace Pms\BudgetBundle\Form;

use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectListType extends AbstractType
{

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
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
                    if($this->user['role'] == 'ROLE_SUPER_ADMIN') {
                    return $repository->createQueryBuilder('p')
                        ->join('p.users', 'u')
                        ->where('p.status = 1')
                        ->orderBy('p.projectName','ASC');
                    } else {
                        return $repository->createQueryBuilder('p')
                            ->join('p.users', 'u')
                            ->where('p.status = 1')
                            ->andWhere('u IN(:user)')
                            ->setParameter('user', $this->user['userId'])
                            ->orderBy('p.projectName','ASC');
                    }
                }
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\BudgetBundle\Entity\Budget'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'budgetprojectlist';
    }
}
