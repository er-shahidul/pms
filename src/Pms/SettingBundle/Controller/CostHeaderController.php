<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\CostHeader;
use Pms\SettingBundle\Form\CostHeaderType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * CostHeader controller.
 *
 */
class CostHeaderController extends Controller
{
    public function indexAction(Request $request, $status = 'active')
    {
        $costHeaderRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader');

        $costHeader = new CostHeader();

        $form = $this->createForm(new CostHeaderType(), $costHeader);

        $costHeaders = $this->paginate($costHeaderRepository->getCostHeaderSearch($status));

        return $this->render('PmsSettingBundle:CostHeader:home.html.twig', array(
            'costHeaders' => $costHeaders,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function refreshAction()
    {
        $costHeaderRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader');

        $costHeaders = $this->paginate($costHeaderRepository->getCostHeaderSearch('active'));

        return $this->render('PmsSettingBundle:CostHeader:refresh.html.twig', array(
            'costHeaders' => $costHeaders,
        ));
    }

    public function deleteAction(CostHeader $costHeader)
    {
        $costHeader->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->update($costHeader);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Cost Header Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('cost_header'));
    }

    public function activeAction(CostHeader $costHeader)
    {
        $costHeader->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->update($costHeader);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Cost Header Successfully Restored'
        );

        return $this->redirect($this->generateUrl('cost_header'));
    }

    public function updateAction(Request $request, CostHeader $costHeader, $status = 'active')
    {
        $costHeaderRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader');

        $form = $this->createForm(new CostHeaderType(), $costHeader);

        $costHeaders = $this->paginate($costHeaderRepository->getCostHeaderSearch($status));

        return $this->render('PmsSettingBundle:CostHeader:home.html.twig', array(
            'costHeaders' => $costHeaders,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function checkAction(Request $request)
    {
        $title = $request->request->get('title');

        $titleCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->findOneBy(
            array('title' => $title )
        );

        if ($titleCheck) {
            $return = array("responseCode" => 200, "cost_header_name" => "Cost Header Title already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "cost_header_name" => "Cost Header Title available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $costHeaderArray = $request->request->get('costHeaderArray');
        $costHeaderArray = explode(',',$costHeaderArray);

        $costHeaderTitle = $costHeaderArray[0];
        $updateId        = $costHeaderArray[1];
        $subCategory     = $costHeaderArray[2];

        if($costHeaderTitle && $subCategory) {
            $costHeader = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->find($updateId);
            $costHeaderTitleCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->findOneBy(
                array('title' => $costHeaderTitle )
            );
            if($costHeader) {
                $costHeader->setTitle($costHeaderTitle);
                $costHeader->setSubCategory($this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->find($subCategory));
                $this->getDoctrine()->getManager()->persist($costHeader);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($costHeaderTitleCheck) {

                $return = array("responseCode" => 200);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $costHeader = new CostHeader();
                $costHeader->setTitle($costHeaderTitle);
                $costHeader->setSubCategory($this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->find($subCategory));
                $costHeader->setCreatedBy($this->getUser());
                $costHeader->setCreatedDate(new \DateTime());
                $costHeader->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->create($costHeader);

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
