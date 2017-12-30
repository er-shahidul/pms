<?php

namespace Pms\CoreBundle\Controller;

use Doctrine\ORM\Repository;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Base controller.
 *
 */
class BaseController extends Controller
{
    public function paginate($dql)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (is_string($dql)) {
            $query = $em->createQuery($dql);
        } else {
            $query = $dql;
        }

        $paginator = $this->get('knp_paginator');
        $value     = $paginator->paginate(
            $query,
            $page = $this->get('request')->query->get('page', 1) /*page number*/,
            20/*limit per page*/
        );

        return $value;
    }

    public function successMessage($massage)
    {
        $this->get('session')->getFlashBag()->add('notice', $massage);
    }
}