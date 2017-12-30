<?php

namespace Pms\InventoryBundle\Form;

use Pms\InventoryBundle\Entity\Repository\TotalReceiveItemRepository;
use Pms\InventoryBundle\Entity\TotalReceiveItem;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeliveryReportSearchFormType extends AbstractType
{
    protected $receiveItems;
    private $user;
    public function __construct($receiveItems,$user)
    {
        $this->receiveItems = $receiveItems;
        $this->user = $user;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', 'choice', array(
                'required'=>false,
                'choices' => $this->receiveItems
            ))
            ->add('project', 'entity', array(
                'class' => 'PmsSettingBundle:Project',
                'property' => 'projectName',
                'required' => false,
                'empty_value' => 'Select Project',
                'empty_data' => null,
                'query_builder' => function (ProjectRepository $repository)
                {
                    if($this->user->hasRole("ROLE_SUPER_ADMIN")) {
                        return $repository->createQueryBuilder('p')
                            ->where('p.status = 1')
                            ->orderBy('p.projectName','ASC');
                    } else {
                        return $repository->createQueryBuilder('p')
                            ->join('p.users', 'u')
                            ->where('p.status = 1')
                            ->andWhere('u IN(:user)')
                            ->setParameter('user', $this->user)
                            ->orderBy('p.projectName','ASC');
                    }
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
            ));
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
