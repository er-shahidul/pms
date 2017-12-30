<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends Controller
{
    public function emailViewAction()
    {
        $queryPr = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->createQueryBuilder('pr')
            ->join('pr.project', 'p')
            ->join('pr.subCategory', 'sc')
            ->select('pr.id')
            ->addSelect('p.projectName')
            ->addSelect('pr.createdDate')
            ->addSelect('pr.status')
            ->addSelect('sc.subCategoryName')
            ->addSelect('pr.approveStatus')
            ->where('pr.approveStatus < 3')
            ->andWhere('pr.status = 1')
            ->orderBy('pr.id', 'DESC');
        $prApprovalPending = $queryPr->getQuery()->getArrayResult();

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
            ->orderBy('pr.id', 'DESC');
        $priClaim = $queryPri->getQuery()->getArrayResult();

        $queryPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
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
            ->andWhere('pri.claimedBy IS NOT NULL')
            ->andWhere('pri.quantity > pri.purchaseOrderQuantity')
            ->orderBy('pr.id', 'DESC');
        $poPending = $queryPo->getQuery()->getResult();

        $queryPoi = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->createQueryBuilder('poi')
            ->join('poi.purchaseOrder', 'po')
            ->join('poi.purchaseRequisitionItem', 'pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->join('pr.subCategory', 'sc')
            ->join('poi.project', 'p')
            ->join('poi.item', 'i')
            ->select('i.itemName')
            ->addSelect('p.projectName')
            ->addSelect('sc.subCategoryName')
            ->addSelect('po.id')
            ->addSelect('po.approvedThreeDate')
            ->where('po.status = 1')
            ->andWhere('po.approveStatus = 3')
            ->andWhere('poi.quantity > poi.totalOrderReceiveQuantity OR poi.totalOrderReceiveQuantity IS NULL')
            ->orderBy('po.id', 'DESC');
        $receivePending = $queryPoi->getQuery()->getResult();

        $queryPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
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

        return $this->render('PmsReportBundle:Email:email.html.twig', array(
            'receivePending' => $receivePending,
            'prApprovalPending' => $prApprovalPending,
            'poApprovalPending' => $poApprovalPending,
            'poPending' => $poPending,
            'priClaim' => $priClaim,
        ));
    }

    public function emailSendAction()
    {
        $queryPr = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->createQueryBuilder('pr')
            ->join('pr.project', 'p')
            ->join('pr.subCategory', 'sc')
            ->select('pr.id')
            ->addSelect('p.projectName')
            ->addSelect('pr.createdDate')
            ->addSelect('pr.status')
            ->addSelect('sc.subCategoryName')
            ->addSelect('pr.approveStatus')
            ->where('pr.approveStatus < 3')
            ->andWhere('pr.status = 1')
            ->orderBy('pr.id', 'DESC');
        $prApprovalPending = $queryPr->getQuery()->getArrayResult();

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
            ->orderBy('pr.id', 'DESC');
        $priClaim = $queryPri->getQuery()->getArrayResult();

        $queryPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
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
            ->andWhere('pri.claimedBy IS NOT NULL')
            ->andWhere('pri.quantity > pri.purchaseOrderQuantity')
            ->orderBy('pr.id', 'DESC');
        $poPending = $queryPo->getQuery()->getResult();

        $queryPoi = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->createQueryBuilder('poi')
            ->join('poi.purchaseOrder', 'po')
            ->join('poi.purchaseRequisitionItem', 'pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->join('pr.subCategory', 'sc')
            ->join('poi.project', 'p')
            ->join('poi.item', 'i')
            ->select('i.itemName')
            ->addSelect('p.projectName')
            ->addSelect('sc.subCategoryName')
            ->addSelect('po.id')
            ->addSelect('po.approvedThreeDate')
            ->where('po.status = 1')
            ->andWhere('po.approveStatus = 3')
            ->andWhere('poi.quantity > poi.totalOrderReceiveQuantity OR poi.totalOrderReceiveQuantity IS NULL')
            ->orderBy('po.id', 'DESC');
        $receivePending = $queryPoi->getQuery()->getResult();

        $queryPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
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

        $emailSend = \Swift_Message::newInstance()
            ->setSubject('NPMS')
            ->setFrom('shahidul@emicrograph.com')
            ->setTo('rahad@nourish-poultry.com')
            ->setBody(
                $this->renderView(
                    'PmsReportBundle:Email:email.html.twig',array(
                        'receivePending' => $receivePending,
                        'prApprovalPending' => $prApprovalPending,
                        'poApprovalPending' => $poApprovalPending,
                        'poPending' => $poPending,
                        'priClaim' => $priClaim,
                )),
                'text/html'
            );

        $email = $this->get('mailer')->send($emailSend);

        if($email){
            return new Response('email sent');
        }
    }

    public function emailTempAction()
    {
        return $this->render('PmsReportBundle:Email:emailTemp.html.twig', array());
    }

    public function sendEmail()
    {
        $queryPr = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->createQueryBuilder('pr')
            ->join('pr.project', 'p')
            ->join('pr.subCategory', 'sc')
            ->select('pr.id')
            ->addSelect('p.projectName')
            ->addSelect('pr.createdDate')
            ->addSelect('pr.status')
            ->addSelect('sc.subCategoryName')
            ->addSelect('pr.approveStatus')
            ->where('pr.approveStatus < 3')
            ->andWhere('pr.status = 1')
            ->orderBy('pr.id', 'DESC');
        $prApprovalPending = $queryPr->getQuery()->getArrayResult();

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
            ->orderBy('pr.id', 'DESC');
        $priClaim = $queryPri->getQuery()->getArrayResult();

        $queryPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
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
            ->andWhere('pri.claimedBy IS NOT NULL')
            ->andWhere('pri.quantity > pri.purchaseOrderQuantity')
            ->orderBy('pr.id', 'DESC');
        $poPending = $queryPo->getQuery()->getResult();

        $queryPoi = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
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
        $receivePending = $queryPoi->getQuery()->getResult();

        $queryPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
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

        $emailSend = \Swift_Message::newInstance()
            ->setSubject('NPMS')
            ->setFrom('shahidul@emicrograph.com')
            ->setTo('rahad@nourish-poultry.com')
            ->setBody(
                $this->renderView(
                    'PmsReportBundle:Email:email.html.twig',array(
                        'receivePending' => $receivePending,
                        'prApprovalPending' => $prApprovalPending,
                        'poApprovalPending' => $poApprovalPending,
                        'poPending' => $poPending,
                        'priClaim' => $priClaim,
                )),
                'text/html'
            );

        $this->get('mailer')->send($emailSend);
    }
}