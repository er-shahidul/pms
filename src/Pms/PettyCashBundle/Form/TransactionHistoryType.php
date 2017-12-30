<?php

namespace Pms\PettyCashBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransactionHistoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('transactionHistoryAmount', 'text', array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('dateOfRequiredText', 'text', array(
                'constraints' => array(
                    new NotBlank(
                        array('message'=>'Date invalid'))
                ),
                'attr' => array(
                    'placeholder' => 'Date'
                ),

                'read_only' =>true
            ))

            ->add('notes', 'text', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'attr' => array(
                    'placeholder' => 'Comments'
                )
            ))
            ->add('transactionType', 'choice', array(
                'constraints' => array(
                    new NotBlank()
                ),
                'empty_value' => 'Select Type',
                'choices'   => array(
                    'cash'     => 'As Cash',
                    'invoice' => 'As Invoice'),

                'required'  => false,
            ))
            ->add('file','file')
            ->add('fileTwo','file')
            ->add('fileThree','file')
            ->add('save','submit');
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\PettyCashBundle\Entity\TransactionHistory'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_pettycashbundle_transactionhistory';
    }
}
