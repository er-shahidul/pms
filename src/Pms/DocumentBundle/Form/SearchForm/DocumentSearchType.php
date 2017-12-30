<?php

namespace Pms\DocumentBundle\Form\SearchForm;

use Pms\DocumentBundle\Entity\Repository\DocumentRepository;
use Pms\UserBundle\Entity\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentSearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('po','hidden')
            ->add('user', 'entity', array(
                'class' => 'UserBundle:User',
                'property' => 'username',
                'required' => false,
                'empty_value' => 'Select User',
                'empty_data' => null,
                'query_builder' => function (UserRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->where('u.enabled = 1')
                        ->orderBy('u.username','ASC');
                }
            ))
            ->add('document', 'hidden')

           /* ->add('document', 'entity', array(
                'class' => 'PmsDocumentBundle:Document',
                'property' => 'title',
                'required' => false,
                'empty_value' => 'Select Document title',
                'empty_data' => null,
                'query_builder' => function (DocumentRepository $repository)
                    {
                        return $repository->createQueryBuilder('d')
                            ->orderBy('d.title','ASC');
                    }
            ))*/
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(

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
