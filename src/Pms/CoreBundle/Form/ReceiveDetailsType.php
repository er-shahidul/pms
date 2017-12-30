<?php

namespace Pms\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReceiveDetailsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('billDate', 'text', array(
                'attr' => array(
                    'placeholder' => 'Date'
                ),
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Bill Date should not be blank'
                    ))),
                'data_class' => null
            ))->addViewTransformer(new DateTimeToStringTransformer(null, null, 'Y-m-d')))
            ->add('billNumber', 'text', array(
                'attr' => array(
                    'placeholder' => 'Bill'
                ),
                'constraints' => array(
                    new NotBlank(array(
                        'message'=>'Bill Number should not be blank'
                    ))),
            ))
            ->add('replySendBackComments', 'textarea', array(
                'attr' => array(
                    'placeholder' => 'Reply Send Back Comments'
                )
            ))
            ->add('submit', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\Receive'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'receive_details';
    }
}
