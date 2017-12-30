<?php

namespace Pms\CoreBundle\Form;

use Pms\SettingBundle\Entity\Repository\ItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PurchaseRequisitionItemType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden_entity', array(
                'class' => 'Pms\CoreBundle\Entity\PurchaseRequisitionItem'
            ))
            ->add('item', 'hidden_entity', array(
                'class' => 'Pms\SettingBundle\Entity\Item'
            ))
            /*->add('item', 'entity', array(
                    'class' => 'PmsSettingBundle:Item',
                    'property' => 'itemName',
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Select Item'
                    ),
                    'empty_data' => null,
                    'query_builder' => function (ItemRepository $repository)
                        {
                            return $repository->createQueryBuilder('s')
                                ->join('s.category', 'ca')
                                ->where('s.status = 1')
                                ->andWhere('ca.id IS NOT NULL')
                                ->orderBy('s.itemName', 'ASC');
                        }
            ))*/
            ->add('quantity', 'text')
            ->add('dateOfRequiredText', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Date'
                    ),
                    'data_class' => null,
                    'read_only' => true
                ))
            ->add('comment', 'textarea',array(
                    'data'=>'No Remarks'
                ))
            ->add('itemType', 'text', array(
                'mapped' => false,
                'data_class' => null,
                'read_only' => true
            ))
            ->add('totalPrice', 'hidden', array(
                'data_class' => null,
                'read_only' => true
            ))
            ->add('UOM', 'text', array(
                'mapped' => false,
                'data_class' => null,
                'read_only' => true
            ))
//            ->add('remove', 'button')
            ->add('isHeadOrLocal', 'choice', array(
                'empty_value' => 'Select one',
                'choices'  => array(
                    'head' => 'Head Office',
                    'local' => 'Local Office'
                ),
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\CoreBundle\Entity\PurchaseRequisitionItem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'purchaserequisitionitem';
    }
}
