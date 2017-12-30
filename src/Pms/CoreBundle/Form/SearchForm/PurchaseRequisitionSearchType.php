<?php

namespace Pms\CoreBundle\Form\SearchForm;

use Pms\CoreBundle\Entity\Repository\PurchaseRequisitionRepository;
use Pms\SettingBundle\Entity\Repository\ItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PurchaseRequisitionSearchType extends AbstractType
{
    private $user;
    private $role;

    public function __construct($user, $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pr', 'hidden')
            ->add('prRef', 'hidden')
            ->add('item','hidden');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
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
