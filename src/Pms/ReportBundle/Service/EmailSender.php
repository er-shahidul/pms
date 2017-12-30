<?php

namespace Pms\ReportBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Controller;

class EmailSender
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Container
     */
    private $container;
    /**
     * @var EasyMailer
     */
    private $mailer;

    public function __construct($doctrine,$container,$mailer)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->mailer = $mailer;
    }

    public function sendEmail()
    {
        $users      = $this->getDoctrine()
            ->getRepository('UserBundle:User')
            ->getAccessUserAllEmail();

        if(!empty($users)) {

            foreach($users as $sendToEmail){

                $prApprovalPendingData = $this->prApprovalPending($sendToEmail['projectId']);

                $priClaimData = $this->priClaim();

                $poPendingData = $this->poPending();
                $receivePendingData = $this->receivePending($sendToEmail['projectId']);

                $poApprovalPending = $this->poApprovalPending();

               /* if(!empty($prApprovalPendingData)){
                    $this->email($sendToEmail, $prApprovalPendingData,$status = 'prApprovalPending');
                }*/

               /*if(!empty($priClaimData)){
                    $this->email($sendToEmail, $priClaimData,$status = 'priClaim');
                }*/

                if(!empty($poPendingData)){
                    $this->email($sendToEmail, $poPendingData,$status = 'poPending');
                }

                /*if(!empty($receivePendingData)){
                    $this->email($sendToEmail, $receivePendingData,$status = 'receivePending');
                }*/

               /* if(!empty($poApprovalPending)){
                    $this->email($sendToEmail, $poApprovalPending,$status = 'poApprovalPending');
                }*/
            }

        }
    }

    public function prApprovalEmail(){

        $access = " g.roles LIKE '%ROLE_PURCHASE_REQUISITION_APPROVE_ONE%'
                 or g.roles LIKE '%ROLE_PURCHASE_REQUISITION_APPROVE_TWO%'
                 or g.roles LIKE '%ROLE_PURCHASE_REQUISITION_APPROVE_THREE%'";

        $users      = $this->getDoctrine()
                           ->getRepository('UserBundle:User')
                           ->getAccessUserForEmail($access);

        if(!empty($users)) {
            $prApprovalPendingData = $this->prApprovalPending();
            if(!empty($prApprovalPendingData)){
                $status = 'prApprovalPending';
                foreach($users as $sendToEmail){
                        $this->email($sendToEmail, $prApprovalPendingData,$status);
                }
            }
        }

    }
    public function poApprovalEmail(){

        $access = "g.roles LIKE '%ROLE_PURCHASE_ORDER_APPROVE_ONE%'
                     or g.roles LIKE '%ROLE_PURCHASE_ORDER_APPROVE_TWO%'
                     or g.roles LIKE '%ROLE_PURCHASE_ORDER_APPROVE_THREE%' ";

        $users      = $this->getDoctrine()
                           ->getRepository('UserBundle:User')
                           ->getAccessUserForEmail($access);

        if(!empty($users)) {
            $poApprovalPending = $this->poApprovalPending();
            if(!empty($poApprovalPending)){

                $status = 'poApprovalPending';
                foreach($users as $sendToEmail){
                        $this->email($sendToEmail, $poApprovalPending,$status);
                }
            }
        }

    }
    public function receivePendingEmail(){

        $access = "g.roles LIKE '%ROLE_RECEIVE_ADD%'";

        $users      = $this->getDoctrine()
                           ->getRepository('UserBundle:User')
                           ->getAccessUserForEmail($access);

        if(!empty($users)) {
            $receivePendingData = $this->receivePending();

            if(!empty($receivePendingData)){

                $status = 'receivePending';
                foreach($users as $sendToEmail){
                        $this->email($sendToEmail, $receivePendingData,$status);
                }
            }
        }

    }
    public function priClaimEmail(){

        $access = "g.roles LIKE '%ROLE_PURCHASE_REQUISITION_CLAIM%'";

        $users      = $this->getDoctrine()
                           ->getRepository('UserBundle:User')
                           ->getAccessUserForEmail($access);

        if(!empty($users)) {
            $priClaimData = $this->priClaim();

            if(!empty($priClaimData)){
                $status = 'priClaim';
                foreach($users as $sendToEmail){
                        $this->email($sendToEmail, $priClaimData,$status);
                }
            }
        }

    }
    public function poPendingEmail(){

        $access = "g.roles LIKE '%ROLE_PURCHASE_REQUISITION_CLAIM%'";

        $users      = $this->getDoctrine()
                           ->getRepository('UserBundle:User')
                           ->getAccessUserForEmail($access);

        if(!empty($users)) {
            $poPendingData = $this->poPending();

            if(!empty($poPendingData)){

                $status = 'poPending';
                foreach($users as $sendToEmail){
                        $this->email($sendToEmail, $poPendingData,$status);
                }
            }
        }

    }


    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private function getDoctrine()
    {
        return $this->doctrine;
    }

    private function renderView($string, $array)
    {
        return $this->container->get('templating')->render($string, $array);
    }

    public function email($sendToEmail, $data,$status)
    {

       $to = $sendToEmail['email'];
        $from = 'npms@nourish-poultry.com';
        $subject ='NPMS';
        $body = $this->renderView(
            'PmsReportBundle:Email:email.html.twig',
            array(
                'EmailInformation' => $data,
                'status' =>$status
            )
        );

        $this->mailer->send($to, $from, $subject, $body);
    }

    public  function prApprovalPending()
    {
        $queryPr = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->createQueryBuilder('pr')
            ->join('pr.project', 'p')
            ->join('pr.subCategory', 'sc')
            ->select('pr.id')
            ->addSelect('p.projectName')
            ->addSelect('p.id as projectId')
            ->addSelect('pr.createdDate')
            ->addSelect('pr.status')
            ->addSelect('sc.subCategoryName')
            ->addSelect('pr.approveStatus')
            ->where('pr.approveStatus < 3')
            ->andWhere('pr.status = 1')
/*            ->andWhere('p.id = :projectId')
            ->setParameter('projectId',$projectId)*/
            ->orderBy('pr.id', 'DESC');
        $prApprovalPending = $queryPr->getQuery()->getArrayResult();

        return $prApprovalPending;
    }

    private function priClaim()
    {
        $queryPri = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
            ->createQueryBuilder('pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->join('pri.item', 'i')
            ->join('pr.project', 'p')
            ->join('pr.subCategory', 'sc')
            ->select('pr.id')
            ->addSelect('p.projectName')
            ->addSelect('i.itemName')
            ->addSelect('pr.approvedDateCategoryHeadTwo')
            ->addSelect('pr.status')
            ->addSelect('sc.subCategoryName')
            ->addSelect('pr.approveStatus')
            ->where('pri.claimedBy is null')
            ->andWhere('pr.approveStatus = 3')
            ->andWhere('pr.status = 1')
            ->andWhere('pr.totalRequisitionItemQuantity != 0')
            ->orderBy('pr.id', 'DESC');
        $priClaim = $queryPri->getQuery()->getArrayResult();

        return $priClaim;
    }

    private function poPending()
    {
        $queryPo   = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
            ->createQueryBuilder('pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->join('pr.subCategory', 'sc')
            ->join('pr.project', 'p')
            ->join('pri.item', 'i')
            ->join('pri.claimedBy', 'u')
            ->select('pr.id')
            ->addSelect('p.projectName')
            ->addSelect('i.itemName')
            ->addSelect('pr.approvedDateCategoryHeadTwo')
            ->addSelect('pr.status')
            ->addSelect('sc.subCategoryName')
            ->addSelect('pr.approveStatus')
            ->addSelect('u.username')
            ->addSelect('pri.claimedDate')
            ->where('pri.status = 1')
            ->andWhere('pr.approveStatus = 3')
            ->andWhere('pr.totalRequisitionItemQuantity != 0')
            ->andWhere('pri.claimedBy IS NOT NULL')
            ->andWhere('pri.quantity > pri.purchaseOrderQuantity')
            ->orderBy('pr.id', 'DESC');
        $poPending = $queryPo->getQuery()->getResult();

        return $poPending;
    }


    private function receivePending()
    {
        $queryPoi       = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->createQueryBuilder('poi')
            ->join('poi.purchaseOrder', 'po')
            ->join('poi.purchaseRequisitionItem', 'pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->join('pr.subCategory', 'sc')
            ->join('poi.project', 'p')
            ->join('poi.item', 'i')
            ->addSelect('i.itemName')
            ->addSelect('p.projectName')
            ->addSelect('sc.subCategoryName')
            ->addSelect('po.id')
            ->addSelect('po.approvedThreeDate')
            ->where('po.status = 1')
            ->andWhere('po.approveStatus = 3')
            ->andWhere('poi.quantity > poi.totalOrderReceiveQuantity OR poi.totalOrderReceiveQuantity IS NULL')
            ->orderBy('po.id', 'DESC');
        $receivePending = $queryPoi->getQuery()->getArrayResult();

        return $receivePending;
    }

    private function poApprovalPending()
    {
        $queryPo           = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
            ->createQueryBuilder('po')
            ->join('po.purchaseOrderItems', 'poi')
            ->leftJoin('po.vendor', 'v')
            ->join('poi.purchaseRequisitionItem', 'pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->leftJoin('pr.project', 'p')
            ->leftJoin('po.buyer', 'b')
            ->select('po.id as poId')
            ->addSelect('b.username')
            ->addSelect('p.projectName')
            ->addSelect('po.createdDate')
            ->addSelect('v.vendorName')
            ->addSelect('po.status')
            ->addSelect('po.approveStatus')
            ->where('po.approveStatus < 3')
            ->andWhere('po.status = 1')
            ->orderBy('po.id', 'DESC')
            ->groupBy('poi.purchaseOrder');
        $poApprovalPending = $queryPo->getQuery()->getArrayResult();

        return $poApprovalPending;
    }
}