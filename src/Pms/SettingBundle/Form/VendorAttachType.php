<?php

namespace Pms\SettingBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VendorAttachType extends AbstractType
{
    /**
     * @var
     */
    private $type;

    /**
     * VendorAttachType constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileType', 'hidden', array(
                'required' => true,
                'data' =>$this->type
            ))
            ->add('fileName', 'text')
            ->add('description', 'textarea')
            ->add('file', 'file')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\SettingBundle\Entity\VendorAttach'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'vendorattach';
    }
}
