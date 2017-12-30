<?php

namespace Pms\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $user = $this->getUser();

        if($user->hasRole("ROLE_DASHBOARD_VIEW")) {


        $em = $this->get('doctrine.orm.entity_manager');


        $pri = $this->paginate10($em->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                ->createQueryBuilder('pri')
                ->orderBy('pri.id', 'DESC')
        );

        $pr = $this->paginate10($em->getRepository('PmsCoreBundle:PurchaseRequisition')
                ->createQueryBuilder('pr')
                ->orderBy('pr.id', 'DESC')
        );

        $po = $this->paginate10($em->getRepository('PmsCoreBundle:PurchaseOrder')
                ->createQueryBuilder('po')
                ->orderBy('po.id', 'DESC')
        );

        $totalPOAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->totalPOAmount();

        $allProjects = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->allProject();

        $allItems = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->allItem();

        $allVendors = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->allVendor();

        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if($mobileDetector->isMobile()){
            return $this->redirect($this->generateUrl('purchase_requisition',array('status'=>'open')));
        }
        return $this->render('UserBundle:Default:index.html.twig', array(
            'pri' => $pri,
            'pr1' => $pr,
            'po' => $po,
            'allProjects' => $allProjects,
            'allItems' => $allItems,
            'allVendors' => $allVendors,
            'totalPOAmount' => $totalPOAmount,
            'openPurchaseRequisitions' => $this->getPurchaseRequisitionOpeningData(),
            'closePurchaseRequisitions' => $this->getPurchaseRequisitionClosingData(),
            'openPurchaseOrders' => $this->getPurchaseOrderOpeningData(),
            'closePurchaseOrders' => $this->getPurchaseOrderClosingData(),
        ));

        } else {
            return $this->render('UserBundle:Default:dashboard.html.twig', array());
        }
    }

    public function getPurchaseRequisitionOpeningData() {
        $prRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition');

        $openPurchaseRequisitions= $this->paginateData($prRepo->getPurchaseRequisitionSearch($this->getUser(), 0, 'open',$month=null));

        return $openPurchaseRequisitions;
    }

    public function getPurchaseRequisitionClosingData() {
        $prRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition');
        $closePurchaseRequisitions = $this->paginateData($prRepo->getPurchaseRequisitionSearch($this->getUser(), 0, 'cancel',$month=null));

        return $closePurchaseRequisitions;
    }

    public function getPurchaseOrderOpeningData(){
        $poRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder');
        $openPurchaseOrders= $this->paginateData($poRepo->getPurchaseOrderSearch($this->getUser(), 0, 'open',$month=null));

        return $openPurchaseOrders;
    }

    public function getPurchaseOrderClosingData(){
        $poRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder');
        $closePurchaseOrders = $this->paginateData($poRepo->getPurchaseOrderSearch($this->getUser(), 0, 'cancel',$month=null));

        return $closePurchaseOrders;
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

    public function paginate10($dql)
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
            10/*limit per page*/
        );

        return $value;
    }

    public function paginateData($dql)
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
            3/*limit per page*/
        );

        return $value;
    }
}