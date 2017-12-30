<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;

use Pms\SettingBundle\Entity\PurchaseType;
use Pms\SettingBundle\Form\PurchaseTypeType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Purchase Type controller.
 *
 */
class PurchaseTypeController extends Controller
{

    public function indexAction(Request $request, $status = 'active')
    {
        $purchaseTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType');

        $purchaseType = new PurchaseType();

        $form = $this->createForm(new PurchaseTypeType(), $purchaseType);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->create($purchaseType);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Purchase Type Add Successfully'
                );

                return $this->redirect($this->generateUrl('purchase_type'));
            }
        }

        $purchaseTypes = $this->paginate($purchaseTypeRepository->getPurchaseTypeSearch($status));

        return $this->render('PmsSettingBundle:PurchaseType:home.html.twig', array(
            'purchaseTypes' => $purchaseTypes,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function deleteAction(PurchaseType $purchaseType)
    {
        $purchaseType->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->update($purchaseType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Item Type Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('purchase_type'));
    }

    public function activeAction(PurchaseType $purchaseType)
    {
        $purchaseType->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->update($purchaseType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Item Type Successfully Restored'
        );

        return $this->redirect($this->generateUrl('purchase_type'));
    }

    public function updateAction(Request $request, PurchaseType $purchaseType, $status = 'active')
    {
        $purchaseTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType');

        $form = $this->createForm(new PurchaseTypeType(), $purchaseType);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->create($purchaseType);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Purchase Type Update Successfully'
                );

                return $this->redirect($this->generateUrl('purchase_type'));
            }
        }

        $purchaseTypes = $this->paginate($purchaseTypeRepository->getPurchaseTypeSearch($status));

        return $this->render('PmsSettingBundle:PurchaseType:home.html.twig', array(
            'purchaseTypes' => $purchaseTypes,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function checkAction(Request $request)
    {
        $purchaseType = $request->request->get('purchasetype');

        $itemTypeCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->findOneBy(
            array('name' => $purchaseType )
        );

        if ($itemTypeCheck) {
            $return = array("responseCode" => 200, "purchaseType_name" => "Purchase Type name already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "purchaseType_name" => "Purchase Type name available.");
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
