<?php

namespace Pms\PettyCashBundle\Form;

use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransactionType extends AbstractType
{
   /* private $amount;
    public function __construct($amount)
    {
        $this->amount = $amount;
    }*/
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('transactionAmount', 'text', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
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
            ->add('transactTo', 'entity', array(
                    'constraints' => array(
                        new NotBlank()
                    ),
                    'class' => 'UserBundle:User',
                    'property' => 'username',
                    'required' => true,
                    'empty_value' => 'Select User',
                    'empty_data' => null,
                    'query_builder' => function (UserRepository $repository)
                        {
                            return $repository->createQueryBuilder('u')
                                ->join('u.groups', 'g')
                                ->where("g.roles LIKE '%ROLE_BANK_TRANSACTION%' or g.roles LIKE '%ROLE_SUPER_ADMIN%'")
                                ->andWhere('u.enabled = 1')
                                ->orderBy('u.username', 'ASC');
                        }
            ))
            ->add('file','file')
            ->add('fileTwo','file')
            ->add('fileThree','file')

            ->add('save','submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\PettyCashBundle\Entity\Transaction'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_pettycashbundle_transaction';
    }
}
