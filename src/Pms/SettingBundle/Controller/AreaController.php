<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\Area;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Pms\SettingBundle\Form\AreaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
/**
 * Area controller.
 *
 */
class AreaController extends Controller
{
//    /** @PreAuthorize("hasRole('ROLE_SUPER_ADMIN')") */
    public function indexAction(Request $request, $status = 'active')
    {
        $areaRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Area');

        $area = new Area();

        $form = $this->createForm(new AreaType(), $area);

        $areas = $this->paginate($areaRepository->getAreaSearch($status));

        return $this->render('PmsSettingBundle:Area:home.html.twig', array(
            'areas' => $areas,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function refreshAction()
    {
        $areaRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Area');

        $areas = $this->paginate($areaRepository->getAreaSearch('active'));

        return $this->render('PmsSettingBundle:Area:refresh.html.twig', array(
            'areas' => $areas,
        ));
    }

    public function deleteAction(Area $area)
    {
        $area->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Area')->update($area);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Area Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('area'));
    }

    public function activeAction(Area $area)
    {
        $area->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Area')->update($area);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Area Successfully Restored'
        );

        return $this->redirect($this->generateUrl('area'));
    }

    public function updateAction(Request $request, Area $area, $status = 'active')
    {
        $areaRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Area');

        $form = $this->createForm(new AreaType(), $area);

        $areas = $this->paginate($areaRepository->getAreaSearch($status));

        return $this->render('PmsSettingBundle:Area:home.html.twig', array(
            'areas' => $areas,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function checkAction(Request $request)
    {
        $areaName = $request->request->get('area');

        $area = $this->getDoctrine()->getRepository('PmsSettingBundle:Area')->findOneBy(
            array('areaName' => $areaName )
        );

        if ($area) {
            $return = array("responseCode" => 200, "area_name" => "Area name already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "area_name" => "Area name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $areaArray = $request->request->get('areaArray');
        $areaArray = explode(',',$areaArray);

        $areaName = $areaArray[0];
        $updateId = $areaArray[1];

        if($areaName) {
            $area = $this->getDoctrine()->getRepository('PmsSettingBundle:Area')->find($updateId);
            $areaNameCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:Area')->findOneBy(
                array('areaName' => $areaName )
            );
            if($area) {
                $area->setAreaName($areaName);
                $this->getDoctrine()->getManager()->persist($area);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($areaNameCheck) {

                $return = array("responseCode" => 200);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $area = new Area();
                $area->setAreaName($areaName);
                $area->setCreatedBy($this->getUser());
                $area->setCreatedDate(new \DateTime());
                $area->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:Area')->create($area);

                $return = array("responseCode" => '404');
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
        } else {
            $return = array("responseCode" => 204);
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function paginate($dql)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (is_string($dql)) {
            $query = $em->createQuery($dql);
        } else {
            $query = $dql;
        }

        $paginator = $this->get('knp_paginator');
        $value = $paginator->paginate(
            $query,
            $page = $this->get('request')->query->get('page', 1) /*page number*/,
            50/*limit per page*/
        );

        return $value;
    }
}