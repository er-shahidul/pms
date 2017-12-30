<?php

namespace Pms\InventoryBundle\Form;

use Pms\SettingBundle\Entity\Repository\ItemRepository;
use Pms\SettingBundle\Entity\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Xiidea\EasyFormBundle;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TotalReceiveItemType extends AbstractType
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
            ->add('openItem', 'text',array(
                'attr' => array(
                    'placeholder' => 'Total Stock In Hand'
                ),
            ))
            ->add('price', 'text',array(
                'attr' => array(
                    'placeholder' => 'Price'
                ),
            ))
           /* ->add('extraAddedItemQuantity', 'text',array(
                'attr' => array(
                    'placeholder' => 'Extra Added Item In Hand'
                ),
            ))*/
            ->add('project', 'entity', array(
                'class' => 'PmsSettingBundle:Project',
                'property' => 'projectName',
                'required' => false,
                'empty_value' => 'Select Project',
                'empty_data' => null,
                'query_builder' => function (ProjectRepository $repository)
                {
                    /*return $repository->createQueryBuilder('p')
                        ->where('p.status = 1')
                        ->orderBy('p.projectName','ASC');*/
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
           ->add('item','entity_hidden',array(
               'attr' => array(
                   'placeholder' => 'Select Item'
               ),
               'class' => 'PmsSettingBundle:Item',
           ))
            /*->add('item', 'entity', array(
                'attr' => array(
                    'class' => 'fontColorBlack'
                ),
                'class' => 'PmsSettingBundle:Item',
                'property' => 'itemName',
                'required' => false,
                'empty_value' => 'Select Item',
                'empty_data' => null,
                'query_builder' => function (ItemRepository $repository)
                {
                    return $repository->createQueryBuilder('i')
                        ->where('i.status = 1')
                        ->orderBy('i.itemName','ASC');
                }
            ))*/
            ->add('submit', 'submit');
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\InventoryBundle\Entity\TotalReceiveItem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_inventorybundle_totalreceiveitem';
    }
}
