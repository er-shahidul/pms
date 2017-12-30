<?php

namespace Pms\CoreBundle\Form;

use Pms\DocumentBundle\Entity\Repository\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReceiveInvoiceBillType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('invoice', 'entity', array(
                'class' => 'PmsDocumentBundle:Document',
                'property' => 'formattedTitle',
                'required' => false,
                'empty_value'=>'Select invoice',
                'empty_data' => null,
                'query_builder' => function (DocumentRepository $repository)
                    {
                        return $repository->createQueryBuilder('d')
                            ->where('d.invoiceOrCalan = 1')
                            ->andWhere('d.billNumber is not null')
                            ->andWhere('d.billAmount is not null')
                            ->andWhere('d.dateOfBill is not null');
                    }
            ))*/
            ->add('invoice', 'hidden_entity',array(
                'class'=>'Pms\DocumentBundle\Entity\Document',
                'attr'=>array(
                    'placeholder' => 'Select Invoice',
                )
            ))
            /*->add('calan', 'entity', array(
                'class' => 'PmsDocumentBundle:Document',
                'property' => 'formattedTitle',
                'required' => false,
                'empty_value'=>'Select Calan',
                'empty_data' => null,
                'query_builder' => function (DocumentRepository $repository)
                    {
                        return $repository->createQueryBuilder('d')
                            ->where('d.invoiceOrCalan = 0')
                            ->andWhere('d.billNumber is not null')
                            ->andWhere('d.billAmount is not null')
                            ->andWhere('d.dateOfBill is not null');
                    }
            ))*/
            ->add('calan', 'hidden_entity',array(
                'class'=>'Pms\DocumentBundle\Entity\Document',
                'attr'=>array(
                    'placeholder' => 'Select Chalan',
                )
            ))
            ->add('save', 'submit')
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
        return 'receive';
    }
}
