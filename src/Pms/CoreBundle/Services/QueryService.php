<?php

namespace Pms\CoreBundle\Services;


use Doctrine\Bundle\DoctrineBundle\Registry;

class QueryService
{
    /**
     * @var Registry
     */
    private $doctrine;


    public function __construct($doctrine)
    {
        $this->doctrine   = $doctrine;
    }

    public function purchaseRequisitionUpdateQuery()
    {
        $purchaseRequisitionItemLists = $this->getDoctrine()

            ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getByUpdatedDate();

         $this->getDoctrine()
              ->getRepository('PmsCoreBundle:PurchaseRequisition')
              ->getByUpdatedDate($purchaseRequisitionItemLists);



    }
    public function purchaseOrderAmendmentUpdateQuery()
    {
        $purchaseOrderItemLists = $this->getDoctrine()

            ->getRepository('PmsCoreBundle:PurchaseOrderItem')->getByAmendment();


         $purchaseOrderList = $this->getDoctrine()
              ->getRepository('PmsCoreBundle:PurchaseOrder')
              ->getPurchaseOrderByAmendment();

        foreach($purchaseOrderItemLists as $purchaseOrderItem) {

            if(!isset($purchaseOrderList[$purchaseOrderItem['amendmentRefPo']])){
                    continue;
                }
            $oldPoId = $purchaseOrderList[$purchaseOrderItem['amendmentRefPo']]['id'];
            $oldOrderItemQty = $purchaseOrderList[$purchaseOrderItem['amendmentRefPo']]['totalOrderItemQuantity'];
            $newPoOrderItemQty = $purchaseOrderItem['TotalUpdateQuantity'];
            $remainingQuantity = $oldOrderItemQty - $newPoOrderItemQty;
            $this->getDoctrine()
                 ->getRepository('PmsCoreBundle:PurchaseOrder')
                 ->updatePurchaseOrderQuery($oldPoId,$remainingQuantity);
        }
    }


    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private function getDoctrine()
    {
        return $this->doctrine;
    }



}