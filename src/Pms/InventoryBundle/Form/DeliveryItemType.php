<?php

namespace Pms\InventoryBundle\Form;

use Pms\SettingBundle\Entity\Repository\ItemRepository;
use Pms\InventoryBundle\Entity\Repository\TotalReceiveItemRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DeliveryItemType extends AbstractType
{

    private $projectId;

    public function __construct($data)
    {
        $this->projectId = $data;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('item', 'entity', array(
                'constraints' => array(
                    new NotBlank(array('message'=>'Item should not be blank'))
                ),
                'class' => 'Pms\SettingBundle\Entity\Item',
                'property' => 'itemName',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Select Item',
                    'class' => 'select2me'
                ),
                'empty_value' => 'Select Item',
                'empty_data' =>null,
                'query_builder' => function (ItemRepository $repository)
                {
                   return $repository->getQueryByProjectId($this->projectId);
                }
            ))
            ->add('quantity', 'text',array(
                'constraints' => array(
                    new NotBlank(
                        array('message'=>'Quantity should not be blank')
                    ),
                   /* new Regex(
                        array(
                            'message'=>'Quantity should be number',
                            'pattern'=>'/^[\d][.]+$/i',
                        )
                    )*/
                )
            ))
            ->add('itemType', 'text', array(
                'mapped' => false,
                'data_class' => null,
                'read_only' => true
            ))
            ->add('remainingQuantity', 'text', array(
                'mapped' => false,
                'data_class' => null,
                'read_only' => true
            ))
            ->add('unit', 'text', array(
                'mapped' => false,
                'data_class' => null,
                'read_only' => true
            ))
            ->add('remove', 'button');
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pms\InventoryBundle\Entity\DeliveryItem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pms_inventorybundle_deliveryitem';
    }
}
