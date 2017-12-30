<?php

namespace Pms\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SystemUsagesSummaryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', 'text',array(
                    'attr' => array(
                        'placeholder' => 'Select Year'
                    ),
                    'data' => '0'
                ))
            ->add('search', 'submit')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection'   => false

        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'system_usages_summary_search';
    }
}
