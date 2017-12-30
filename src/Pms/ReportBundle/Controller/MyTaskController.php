<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\CoreBundle\Entity\PurchaseRequisitionItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class MyTaskController extends Controller
{
    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->container->get('security.context');
    }

    public function myTaskAction()
    {
        $em = $this->getDoctrine()->getManager();
        $grantedAllRule = $this->getSecurityContext()->getToken();
        $user = $this->get('security.context')->getToken()->getUser()->getId();
        $userRole = $this->get('security.context')->getToken()->getUser()->getRole();

        $grantedSuperAdmin = $this->getSecurityContext()->isGranted('ROLE_SUPER_ADMIN');

        $grantedRequisitionApprovalOne = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_REQUISITION_APPROVE_ONE');
        $grantedRequisitionApprovalTwo = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_REQUISITION_APPROVE_TWO');
        $grantedRequisitionApprovalThree = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_REQUISITION_APPROVE_THREE');
        $grantedRequisitionClaim = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_REQUISITION_CLAIM');
        $grantedRequisitionClose = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_REQUISITION_CLOSE');

        $grantedOrderApprovalOne = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_ORDER_APPROVE_ONE');
        $grantedOrderApprovalTwo = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_ORDER_APPROVE_TWO');
        $grantedOrderApprovalThree = $this->getSecurityContext()->isGranted('ROLE_PURCHASE_ORDER_APPROVE_THREE');

        $grantedBudgetApprovalOne = $this->getSecurityContext()->isGranted('ROLE_BUDGET_APPROVE_ONE');
        $grantedBudgetApprovalTwo = $this->getSecurityContext()->isGranted('ROLE_BUDGET_APPROVE_TWO');
        $grantedBudgetApprovalThree = $this->getSecurityContext()->isGranted('ROLE_BUDGET_APPROVE_THREE');

        $grantedReceiveAdd = $this->getSecurityContext()->isGranted('ROLE_RECEIVE_ADD');
        $grantedReceiveDelivery = $this->getSecurityContext()->isGranted('ROLE_RECEIVE_DELIVERY');

        $query = $em->getRepository('UserBundle:User')
            ->createQueryBuilder('u')
            ->join('u.projects', 'p')
            ->select('p.projectName')
            ->addSelect('u.id')
            ->andWhere("u.id = :user")
            ->setParameter('user', $user);
        $userProjects = $query->getQuery()->getResult();

        $prRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition');
        $allPurchaseRequisitions = $this->paginate($prRepo->getPurchaseRequisitionSearch($this->getUser(), 0, 0,$month = null));

        $poRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder');
        $allPurchaseOrders = $this->paginate($poRepo->getPurchaseOrderSearch($this->getUser(), 0, 0,$month = null));

        if($grantedSuperAdmin){
            $query1 = $em->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                ->createQueryBuilder('pri')
                ->join('pri.purchaseRequisition', 'pr')
                ->join('pri.purchaseOrderItems', 'poi')
                ->join('pr.project', 'p')
                ->join('p.users', 'u')
                ->join('u.groups', 'g')
                ->where('pri.status = 1')
                ->andWhere('pri.poApprovalStatus = 3')
                ->andWhere('poi.approvalStatus = 3')
                ->andWhere('poi.totalOrderReceiveQuantity is null or pri.quantity > poi.totalOrderReceiveQuantity')
                ->andWhere('poi.status = 1');
            $allReceives = $query1->getQuery()->getResult();
        }else{
            $query2 = $em->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                ->createQueryBuilder('pri')
                ->join('pri.purchaseRequisition', 'pr')
                ->join('pri.purchaseOrderItems', 'poi')
                ->join('pr.project', 'p')
                ->join('p.users', 'u')
                ->join('u.groups', 'g')
                ->where('pri.status = 1')
                ->andWhere('pri.poApprovalStatus = 3')
                ->andWhere('poi.approvalStatus = 3')
                ->andWhere('poi.totalOrderReceiveQuantity is null or pri.quantity > poi.totalOrderReceiveQuantity')
                ->andWhere('poi.status = 1')
                ->andWhere("u IN(:user)")
                ->setParameter('user', $user);
            $allReceives = $query2->getQuery()->getResult();
        }

        if($grantedSuperAdmin){
            $query3 = $em->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                ->createQueryBuilder('pri')
                ->join('pri.purchaseRequisition', 'pr')
                ->join('pr.project', 'p')
                ->join('p.users', 'u')
                ->join('u.groups', 'g')
                ->where('pri.status = 1')
                ->andWhere("pri.claimedBy IS NULL");
            $allClaims = $query3->getQuery()->getResult();
        }else{
            $query4 = $em->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                ->createQueryBuilder('pri')
                ->join('pri.purchaseRequisition', 'pr')
                ->join('pr.project', 'p')
                ->join('p.users', 'u')
                ->join('u.groups', 'g')
                ->where('pri.status = 1')
                ->andWhere("pri.claimedBy IS NULL")
                ->andWhere("u IN(:user)")
                ->setParameter('user', $user);
            $allClaims = $query4->getQuery()->getResult();
        }

        return $this->render('PmsReportBundle:MyTask:myTask.html.twig', array(
            'allPurchaseRequisitions' => $allPurchaseRequisitions,
            'allPurchaseOrders' => $allPurchaseOrders,
            'allReceives' => $allReceives,
            'allClaims' => $allClaims,
        ));
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