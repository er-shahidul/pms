<?php

namespace Pms\ReportBundle\Service;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Util\Debug;
use Symfony\Component\PropertyAccess\PropertyAccess;


class SmsSender
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var SmsGateWay
     */
    private $smsGateWay;

    public function __construct($doctrine, SmsGateWay $smsGateWay)
    {
        $this->doctrine   = $doctrine;
        $this->smsGateWay = $smsGateWay;
    }

    public function smsPlanForDirectorMdFdAction()
    {
        $month  = date('Y-m');

        $purchaseRequisitionMonthlyCosts = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->getPurchaseRequisitionMonthlyCost($month);

        $budgetMonthlyCosts = $this->getDoctrine()
            ->getRepository('PmsBudgetBundle:Budget')
            ->getBudgetMonthlyCost($month);


        $users = $this->getDoctrine()->getRepository('UserBundle:User')->getAccessUserBudgetIndexedByProject();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach($purchaseRequisitionMonthlyCosts as $prCostItem) {

            if(!isset($users[$prCostItem['id']])) {
                continue;
            }

            $budgetAmount = $propertyAccessor->getValue($budgetMonthlyCosts, "[{$prCostItem['id']}][{$prCostItem['subId']}]['amount']");

            if(floatval($budgetAmount) < floatval($prCostItem['prCost']) ) {
                $amount = $prCostItem['prCost'] - $budgetAmount;

                $msg = $prCostItem['projectName']. ' '.
                    $prCostItem['subCategoryName'].
                    ' cross the monthly budget '.'amount '.$amount;

                  $this->sendBudgetCostToPermitedUser($users[$prCostItem['id']], $msg);
            }
        }
    }

    public function smsPlanForApproverRequisitionAction()
    {
           $this->forPrValidate();
           $this->verifiedPr(1);
           $this->forApprovePr(2);
           $this->forPoPendingClaimValidate(0);
           $this->forPoPendingClaimVerified(1);
           $this->forPoPendingClaimApprove(2);
           $this->forPrClaim();
           $this->pendingPoIssue();
    }

    public function pendingForReceivingToStoreOfficerAction()
    {
        $poiDataInfo = $this->getDoctrine()
                             ->getRepository('PmsCoreBundle:PurchaseOrderItem')
                             ->getReceivingItem();

        if(!empty($poiDataInfo)){
            $msg      = 'Purchased Item waiting for receive';
            $this->sendReceivingToStoreOfficerMsg($msg);
        }

    }

    public function forPrValidate()
    {
        $purchaseRequisitionInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseRequisition'
        )->getAllRequisitionByProjectWiseCreated();

        if(!empty($purchaseRequisitionInfo)){
            $msg = 'Purchase Requisition waiting for Verification.';
            $this->sendPrValidateMsg($msg);
        }
    }

    public function verifiedPr($status)
    {
        $purchaseRequisitionInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseRequisition'
        )->getAllRequisitionByProjectWiseVerified($status);

        if(!empty($purchaseRequisitionInfo)){
            $msg = 'Purchase Requisition waiting for  Validation.';
            $this->sendPrVerifiedMsg($msg);
        }

    }

    public function forApprovePr($status)
    {
        $purchaseRequisitionInfos = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseRequisition'
        )->getAllRequisitionByProjectWiseApproved($status);

        if(!empty($purchaseRequisitionInfos)){
            $this->sendApprovePr();
        }
    }

    public function forPoPendingClaimValidate($approve_status)
    {

        $purchaseOrderInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseOrder'
        )->getAllPurchasePendingPo($approve_status);

        if(!empty($purchaseOrderInfo)){

        $msg      = 'Purchase Order  waiting for Verification.';
        $this->sendPendingClaim($msg,'ROLE_PURCHASE_ORDER_APPROVE_ONE');
        }

    }
    public function forPoPendingClaimVerified($approve_status)
    {
        $purchaseOrderInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseOrder'
        )->getAllPurchasePendingPo($approve_status);
        if(!empty($purchaseOrderInfo)){
            $msg = 'Purchase Order waiting  for Validation.';
            $this->sendPendingClaim($msg,'ROLE_PURCHASE_ORDER_APPROVE_TWO');
        }
    }
    public function forPoPendingClaimApprove($approve_status)
    {
        $purchaseOrderInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseOrder'
        )->getAllPurchasePendingPo($approve_status);
        if(!empty($purchaseOrderInfo)){
            $msg = 'Purchase Order waiting for your approval';
            $this->sendPendingClaim($msg,'ROLE_PURCHASE_ORDER_APPROVE_THREE');
        }
    }

    public function pendingPoIssue()
    {
        $purchaseRequisitionInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseRequisition'
        )->getAllPurchaseRequisitionPoIssued();

        if(!empty($purchaseRequisitionInfo)){
            $msg    = 'Approved Purchase Requisition waiting for PO issue';
            $this->sendPendingClaim($msg,'ROLE_PURCHASE_REQUISITION_CLAIM');
        }
    }
    public function forPrClaim()
    {
        $purchaseRequisitionInfo = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:PurchaseRequisition'
        )->getAllPurchaseRequisitionForClaim(3);
        if(!empty($purchaseRequisitionInfo)){
            $msg    = 'Approved Purchase Requisition waiting for your Claim';
            $this->sendPendingClaim($msg,'ROLE_PURCHASE_REQUISITION_CLAIM');
        }

    }

    public function sendPaymentVerification()
    {
        $msg = $this->getPaymentVerificationMessage();
        $users      = $this->getDoctrine()
            ->getRepository('UserBundle:User')
            ->getPaymentVerifiedAccessUser();

        if (!empty($users)) {
            foreach ($users as $cellPhone) {
                $this->smsGateWay->send($msg, '88'.$cellPhone['cellphone']);
            }
        }
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return string
     */
    private function getPaymentVerificationMessage()
    {
        $verifiedInfos = $this->getDoctrine()->getRepository(
            'PmsCoreBundle:ReadyForPayment'
        )->getPaymentVerifiedListFromReceiveSection();
        $billNumber    = array();
        foreach ($verifiedInfos as $verifiedInfo) {
            $billNumber[] = $verifiedInfo['billNumber'];
        }
        $bill = implode(',', $billNumber);
        $text = $bill.' bill no waiting for payment verify';

        return $text;
    }

    /**
     * @param $users
     * @param $prNo
     */
    private function sendBudgetCostToPermitedUser($users, $prNo)
    {
        if (!empty($users)) {
            foreach ($users as $userPhone) {
                $this->smsGateWay->send($prNo, '88'.$userPhone['cellphone']);
            }
        }
    }

    /**
     * @param $projectManager
     * @param $prNo
     */
    private function sendBudgetCostToProjectManager($projectManager, $prNo)
    {
        if (!empty($projectManager)) {
            foreach ($projectManager as $userPhoneManager) {
                $this->smsGateWay->send($prNo, '88'.$userPhoneManager['cellphone']);
            }
        }
    }

    /**
     * @param $projectId
     * @return array
     */
    private function getUserAccessData($projectId)
    {

        $users          = $this->getDoctrine()
            ->getRepository('UserBundle:User')
            ->getAccessUserBudget(
                $projectId
            );

       /* $projectManager = $this->getDoctrine()
            ->getRepository('PmsSettingBundle:Project')
            ->getAccessUserBudgetForProjectManager(
                $projectId
            );*/
        return $users;
    }


    private function sendPrValidateMsg($msg)
    {
        $users = $this->getDoctrine()
                      ->getRepository('UserBundle:User')
                      ->getAccessUserAll($groupBy='ROLE_PURCHASE_REQUISITION_APPROVE_ONE');

        $this->sendSmsToUser($msg, $users);
    }
    private function sendReceivingToStoreOfficerMsg($msg)
    {
        $users = $this->getDoctrine()
                      ->getRepository('UserBundle:User')
                      ->getReceivingAccessUser($groupBy = 'ROLE_RECEIVE_ADD');
        $this->sendSmsToUser($msg, $users);
    }

    private function sendPrVerifiedMsg($msg)
    {
        $users = $this->getDoctrine()
                      ->getRepository('UserBundle:User')
                      ->getAccessUserAll($groupBy = 'ROLE_PURCHASE_REQUISITION_APPROVE_TWO');
        $this->sendSmsToUser($msg, $users);
    }

    /**
     * @param $msg
     * @param $users
     */
    private function sendSmsToUser($msg, $users)
    {
        if (!empty($users)) {
            foreach ($users as $cellPhone) {
                if($cellPhone){
                $this->smsGateWay->send($msg, '88'.$cellPhone['cellphone']);
                }
            }
        }
    }


    private function sendApprovePr()
    {
        $users = $this->getDoctrine()
                      ->getRepository('UserBundle:User')
                      ->getAccessUserAll('ROLE_PURCHASE_REQUISITION_APPROVE_THREE');
        $msg = 'Purchase Requisition waiting for your approval';
        $this->sendSmsToUser($msg, $users);
    }

    private function sendPendingClaim($msg,$groupBy)
    {
        $users = $this->getDoctrine()
                      ->getRepository('UserBundle:User')
                      ->getAccessUserAll($groupBy);
        $this->sendSmsToUser($msg, $users);
    }


}