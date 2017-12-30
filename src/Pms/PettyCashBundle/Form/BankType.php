<?php

namespace Pms\PettyCashBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BankType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', 'choice', array(
                    'empty_value' => 'Select Status',
                    'choices'   => array(
                        'add'     => 'Add',
                        'subtract' => 'Subtract'),
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'required'  => false,
            ))
            ->add('amount', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'attr' => array(
                        'placeholder' => 'Amount'
                    )
            ))
            ->add('notes', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'attr' => array(
                        'placeholder' => 'Notes'
                    )
            ))
            ->add('save','submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\PettyCashBundle\Entity\Bank'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_pettycashbundle_bank';
    }
}
