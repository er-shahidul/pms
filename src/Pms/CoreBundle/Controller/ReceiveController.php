<?php

namespace Pms\CoreBundle\Controller;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\Repository;
use Knp\Snappy\Pdf;
use Pms\CoreBundle\Entity\Log\ReceiveLog;
use Pms\CoreBundle\Entity\PurchaseOrder;
use Pms\CoreBundle\Entity\PurchaseOrderItem;
use Pms\CoreBundle\Entity\PurchaseOrderItemClose;
use Pms\CoreBundle\Entity\PurchaseRequisitionItem;
use Pms\CoreBundle\Entity\ReadyForPayment;
use Pms\CoreBundle\Entity\Receive;
use Pms\CoreBundle\Entity\ReceivedItem;
use Pms\CoreBundle\Form\PurchaseOrderAmendmentType;
use Pms\CoreBundle\Form\ReceiveInvoiceBillType;
use Pms\CoreBundle\Form\ReceiveType;
use Pms\CoreBundle\Form\ReceiveDetailsType;
use Doctrine\DBAL\Connection;

use Pms\CoreBundle\Form\SearchForm\ReceiveSearchType;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Pms\InventoryBundle\Entity\TotalReceiveItem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Receive controller.
 *
 */
class ReceiveController extends BaseController
{
    public function indexAction(Request $request, $status = 'received')
    {
        $rRepo = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive');

        $form = new ReceiveSearchType();

        $data = $request->query->get($form->getName());

        $receives = $this->paginate($rRepo->getReceive($this->getUser(), $data, $status));

        $formSearch = $this->createForm($form, $data);

        return $this->render('PmsCoreBundle:Receive:home.html.twig', array(
            'receives' => $receives,
            'status' => $status,
            'formSearch' => $formSearch->createView(),
        ));
    }

    public function detailsAction(Receive $receive)
    {
        $form = $this->createForm(new ReceiveDetailsType(), $receive);
        $totalPoAmount = $this->getDoctrine()
                              ->getRepository('PmsCoreBundle:Receive')
                              ->getTotalPoAmount($receive);

        return $this->render('PmsCoreBundle:Receive:details.html.twig', array(
            'receive' => $receive,
            'totalPoAmount'=>$totalPoAmount,
            'form' => $form->createView(),
        ));
    }
    public function grnDetailsAction(Receive $receive)
    {

        $form = $this->createForm(new ReceiveDetailsType(), $receive);

        return $this->render('PmsCoreBundle:Receive:grnDetails.html.twig', array(
            'receive' => $receive,
            'form' => $form->createView(),
        ));
    }

    public function verifyAction(Request $request)
    {

        $grnNumbers = $request->request->get('list');


        if ($request->getMethod() == 'POST' && !empty($grnNumbers)) {

            $formDetails = new ReceiveDetailsType();
            $invoice = $this->getDoctrine()
                            ->getRepository('PmsDocumentBundle:Document')
                            ->getInvoiceOrChallan($status='invoice');

            $challan = $this->getDoctrine()
                            ->getRepository('PmsDocumentBundle:Document')
                             ->getInvoiceOrChallan($status='challan');

            foreach($grnNumbers as $key => $grnNumber){
                $grnList[] = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->find($grnNumber);
                $grnFormListInvoice[] = $invoice;
                $grnFormListChallan[] = $challan;
            }

            $form = $this->createForm($formDetails);

            return $this->render('PmsCoreBundle:Receive:verify.html.twig', array(
                'grnList' => $grnList,
                'grnFormListInvoice' => $grnFormListInvoice,
                'grnFormListChallan' => $grnFormListChallan,
                'grnNumbers' => json_encode($grnNumbers),
                'form' => $form->createView(),
            ));
        }

        return $this->redirect($this->generateUrl('receive'));
    }

    public function billAddAction(Request $request)
    {
        $billNumber = $request->request->get('receive_details')['billNumber'];
        $billDate = $request->request->get('receive_details')['billDate'];
        $grnNumbers = json_decode($request->request->get('grnNumbers'), true);
        $billDate = new \DateTime($billDate);
        $invoice = $request->request->get('invoice');
        $challan = $request->request->get('challan');

        foreach($grnNumbers as $key => $grnNumber){

            $receive = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->find($grnNumber);

            if(!empty($receive->getSendBackStatus())){
                $this->receiveItemUpdateForReadyPayment($invoice, $key, $challan, $receive, $billDate, $billNumber);
            } else{

                $this->receiveItemCreateForReadyPayment($invoice, $key, $challan, $receive, $billDate, $billNumber);
            }

        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Receive verified successfully'
        );

        return $this->redirect($this->generateUrl('receive'));
    }



    public function detailsForMobileAction(Receive $receive)
    {
        $form = $this->createForm(new ReceiveDetailsType(), $receive);

        return $this->render('PmsCoreBundle:Receive:detailsForMobile.html.twig', array(
            'receive' => $receive,
            'form' => $form->createView(),
        ));
    }

    public function projectListAction()
    {
        $userProjects = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')
                             ->getProjectForUser($this->getUser());

        return $this->render('PmsCoreBundle:Receive:projectList.html.twig', array(
            'userProjects' => $userProjects,
        ));
    }

    public function printAction(Receive $receive)
    {
        return $this->render('PmsCoreBundle:Receive:print.html.twig', array(
            'receive' => $receive,
        ));
    }

    public function attachmentViewAction(Request $request, Receive $receive, $index)
    {
        if(null !== $response = $this->checkAttachFileByIndex($receive, $index)) {
            return $response;
        }

        if($index == 1){
            $path = $receive->getPath();
        }elseif($index == 2){
            $path = $receive->getPathTwo();
        }elseif($index == 3){
            $path = $receive->getPathThree();
        }

        return $this->render('PmsCoreBundle:Receive:viewer.html.twig', array(
            'r' => $receive,
            'path' => $path,
        ));
    }

    public function billNumberAddAction(Request $request, Receive $receive)
    {
//        $billNumber = $request->request->get("receive_details['billNumber']", null,  true); //need to be like this//
        $billNumber = $request->request->get('receive_details')['billNumber'];
        $billDate = $request->request->get('receive_details')['billDate'];
        $billDate = new \DateTime($billDate);

        $receive->setBillDate($billDate);
        $receive->setBillNumber($billNumber);

        $receive->setStatus(3);
        $receive->setClosedDate(new \DateTime());

        $receive->setApproveStatus(1);
        $receive->setApprovedOne($this->getUser());
        $receive->setApprovedOneDate(new \DateTime());

        $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->update($receive);

        $this->keepLog($receive, 3);
        $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->createReceive($receive);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Receive verified successfully'
        );

        return $this->redirect($this->generateUrl('receive'));
    }

    public function pdfAction(Receive $receive)
    {
        $html = $this->renderView(
            'PmsCoreBundle:Receive:print.html.twig', array(
                'receive' => $receive,
            )
        );

        $wkhtmltopdfPath = 'xvfb-run --server-args="-screen 0, 1280x1024x24" /usr/bin/wkhtmltopdf --use-xserver';
        $snappy          = new Pdf($wkhtmltopdfPath);
        $pdf             = $snappy->getOutputFromHtml($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="receive.pdf"');
        echo $pdf;

        return new Response('');
    }

    public function readyForPaymentAction(Receive $receive)
    {
        $receive->setStatus(3);
        $receive->setClosedDate(new \DateTime());
        $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->update($receive);

        $this->keepLog($receive, 3);
        $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->createReceive($receive);
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Ready For Payment'
        );

        return $this->redirect($this->generateUrl('receive'));
    }

    public function receiveGrnAutoCompleteAction(Request $request){

        $grnNo = $_REQUEST['q'];

        if ($grnNo) {
            $grnNoQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')
                ->grnNoAutoComplete($grnNo, $this->getUser());

        }

        return new JsonResponse($grnNoQuery);
    }

    /**
     * @param Receive $receive
     * @param $status
     */
    protected function keepLog(Receive $receive, $status)
    {
        $receiveLog = new ReceiveLog();

        $receiveLog->setReceive($receive);
        $receiveLog->setCreatedBy($this->getUser());
        $receiveLog->setStatus($status);
        $this->getDoctrine()->getRepository('PmsCoreBundle:Log\ReceiveLog')->create($receiveLog);
    }

    /**
     * @param $orderItems
     * @return Response
     */
    protected function formAmendment($orderItems)
    {
        $purchaseOrder = new PurchaseOrder();
        $orderSplit = explode('/', $orderItems[0]);
        $poId = $orderSplit[5];


        list($itemNameArr, $quantityArr, $itemIdArr, $itemUnitArr, $projectNameArr, $projectHeadCell, $purchaseRequisitionNumberArr, $projectAddressArr) = $this->getAmendmentFormContent($orderItems, $purchaseOrder);

        $form = $this->createForm(new PurchaseOrderAmendmentType(), $purchaseOrder);

        return $this->render('PmsCoreBundle:PurchaseOrder:form.amendment.html.twig', array(
            'orderItems' => json_encode($orderItems, JSON_FORCE_OBJECT),
            'item' => $itemNameArr,
            'quantityArr' => $quantityArr,
            'itemId' => $itemIdArr,
            'project' => $projectNameArr,
            'projectHeadCell' => $projectHeadCell,
            'number' => $purchaseRequisitionNumberArr,
            'address' => $projectAddressArr,
            'itemUnit' => $itemUnitArr,
//            'po'=>$po,
            'form' => $form->createView(),
        ));
    }

    public function saveAmendmentAction(Request $request)
    {

        $orderItemsObj = $_POST['orderItems'];
        $orderItems = json_decode($orderItemsObj, true);

        $purchaseOrder = new PurchaseOrder();

        list($itemNameArr, $quantityArr, $itemIdArr, $itemUnitArr, $projectNameArr, $projectHeadCell, $purchaseRequisitionNumberArr, $projectAddressArr) = $this->getAmendmentFormContent($orderItems, $purchaseOrder);

        $form = $this->createForm(new PurchaseOrderAmendmentType(), $purchaseOrder);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $purchaseOrder->setCreatedBy($this->getUser());
                $purchaseOrder->setCreatedDate(new \DateTime());
                $purchaseOrder->setStatus(1);
                $purchaseOrder->setAmendmentStatus(7);
                $purchaseOrder->setPaymentFrom($this->selectPurchaseOrderPaymentFromType($request));
                $purchaseOrder->setApproveStatus(0);
                $purchaseOrder->setTotalOrderItem(0);
                $purchaseOrder->setTotalOrderItemQuantity(0);

                $k = 0;
                $oldOrderItemQuantityUpdate = 0;
                /** @var PurchaseOrderItem $item */
                foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {

                    $oldOrderItemQuantityUpdate += $item->getQuantity();
                    $item->setAmendmentQuantity(0);

                    $orderItemRef = explode('/', $orderItems[$k]);
                    $poiIdRef = $orderItemRef[1];
                    $poiIdRefPo = $orderItemRef[5];
                    $item->setAmendmentRef($poiIdRef);

                    $item->setPurchaseOrder($purchaseOrder);
                    $item->setStatus(1);
                    $item->setAmendment(7);
                    $item->setAmendmentRefPo($poiIdRefPo);

                    $purchaseOrder->setAmendmentByPoNo($poiIdRefPo);

                    $purchaseOrder->setTotalOrderItem(1 + $purchaseOrder->getTotalOrderItem());
                    $purchaseOrder->setTotalOrderItemQuantity(
                        $item->getQuantity() + $purchaseOrder->getTotalOrderItemQuantity()
                    );

                    $k = $k + 1;
                }

                // update purchase order
                $oldPo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($orderItemRef[5]);
                $oldPo->setTotalOrderItemQuantity($oldPo->getTotalOrderItemQuantity() - $oldOrderItemQuantityUpdate);
                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($oldPo);

                $newRef1 = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->create($purchaseOrder);
                $newRef = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($newRef1);

                $n = 0;
                foreach ($newRef->getPurchaseOrderItems() as $item1) {

                    $orderItemRef = explode('/', $orderItems[$n]);
                    $poiIdRef = $orderItemRef[1];

                    $purchaseOrderItemForAmendment1 = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->find($poiIdRef);
                    $qty = $_POST['purchaseorder']['purchaseOrderItems'][$n]['quantity'];

                    $netTotal = $purchaseOrderItemForAmendment1->getPurchaseOrder()->getNetTotal();
                    $subTotal = $purchaseOrderItemForAmendment1->getPurchaseOrder()->getSubTotal();

                    $purchaseOrderItemForAmendment1->getPrice();



                    $purchaseOrderItemForAmendment1->setAmendment(8);
                    $purchaseOrderItemForAmendment1->setAmendmentQuantity($qty);

                    $purchaseOrderItemForAmendment1->setQuantity(
                        $purchaseOrderItemForAmendment1->getQuantity() - $qty
                    );

                    $amount = $purchaseOrderItemForAmendment1->getQuantity() * $purchaseOrderItemForAmendment1->getPrice();

                    $purchaseOrderItemForAmendment1->setAmendmentRef($item1->getId());
                    $purchaseOrderItemForAmendment1->setAmount($amount);

                    $purchaseOrderItemForAmendment1->getPurchaseOrder()->setNetTotal($netTotal - ($qty * $purchaseOrderItemForAmendment1->getPrice()));
                    $purchaseOrderItemForAmendment1->getPurchaseOrder()->setSubTotal($subTotal - ($qty * $purchaseOrderItemForAmendment1->getPrice()));

                    $this->getDoctrine()->getManager()->persist($purchaseOrderItemForAmendment1);
                    $this->getDoctrine()->getManager()->flush();

                    $n = $n + 1;
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Purchase Order Successfully Amendment'
                );

                return $this->redirect($this->generateUrl('purchase_order', array('status' => 'all') ));
            }
        }

        return $this->render('PmsCoreBundle:PurchaseOrder:form.amendment.html.twig', array(
            'orderItems' => json_encode($orderItems, JSON_FORCE_OBJECT),
            'item' => $itemNameArr,
            'quantityArr' => $quantityArr,
            'itemId' => $itemIdArr,
            'project' => $projectNameArr,
            'projectHeadCell' => $projectHeadCell,
            'number' => $purchaseRequisitionNumberArr,
            'address' => $projectAddressArr,
            'itemUnit' => $itemUnitArr,
            'form' => $form->createView(),
        ));
    }

    public function selectPurchaseOrderPaymentFromType($request){

        $paymentFrom = $request->request->all()['purchaseorder']['paymentFrom'];
        if($paymentFrom == 'Local Office'){
            return 1;
        } else {
            return 2;
        }
    }

    public function refreshAction(Receive $receive)
    {
        return $this->render('PmsCoreBundle:Receive:refresh.html.twig', array(
            'receive' => $receive,
        ));
    }

    public function invoiceBillUpdateAction(Request $request, Receive $receive)
    {
        $form = $this->createForm(new ReceiveInvoiceBillType(),$receive);
        return $this->render('PmsCoreBundle:Receive:invoiceBillUpdate.html.twig', array(
            'entity' => $receive,
            'id'=>$receive->getId(),
            'form' => $form->createView()
        ));
    }

    public function invoiceReceiveBillUpdateAction(Request $request,$id)
    {
        $data = $request->request->all();

        $entity = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->find($id);
        $entity->upload();
        $entity->setInvoice($this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($data['receive']['invoice']));
        $entity->setCalan($this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($data['receive']['calan']));
        $this->getDoctrine()->getRepository("PmsCoreBundle:Receive")->update($entity);
        $massage = 'Invoice/bill file Attach successfully updated';
        $this->successMessage($massage);
        return $this->redirect($this->generateUrl('receive'));
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

    private function checkAttachFileByIndex(Receive $receive, $index)
    {
        if (null == $fileName = $receive->getAbsolutePathByIndex($index)) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function detailsForPaymentAction(Receive $receive, $status)
    {
        $banks = $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')->findBy(array('status'=>1),array('name'=>'asc'));

        $poId = $receive->getPoOrder();
        $advancePayment = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->getAdvancePayment($poId);

        return $this->render('PmsCoreBundle:Payment:detailsPaymentReceive.html.twig', array(
            'receive' => $receive,
            'advancePayment' =>$advancePayment,
            'banks' => $banks,
            'status' => $status
        ));
    }

    public function advancePaymentAction()
    {
        $advance = $this->paginate($this->getDoctrine()
                                        ->getRepository('PmsCoreBundle:PurchaseOrder')
                                         ->getAdvancePayment($this->getUser()));

        return $this->render('PmsCoreBundle:Payment:homePaymentAdvance.html.twig', array(
            'purchaseOrders' => $advance,
            'status' => "advance-payment",
        ));
    }

    public function advanceArchivePaymentAction()
    {
        $advanceArchive = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->getAdvanceArchivePaid());

        return $this->render('PmsCoreBundle:Payment:homePaymentAdvanceArchive.html.twig', array(
            'entities' => $advanceArchive,
            'status' => "advance-archive-payment",
        ));
    }

    public function advanceReceivePaymentAction(PurchaseOrder $purchaseOrder, $status)
    {

        $banks = $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')->findBy(array('status'=>1),array('name'=>'asc'));
        $poId = $purchaseOrder->getId();
        $payment = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->getAdvancePaid($poId);

        return $this->render('PmsCoreBundle:Payment:detailsPaymentAdvance.html.twig', array(
            'purchaseOrder' => $purchaseOrder,
            'advancePayment'=>$payment,
            'banks'=>$banks,
            'status'=>$status
        ));
    }

    private function newAdd($projectId)
    {
        $pri = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
                    ->grantedAddItemListForReceive($this->getUser(), $projectId);
        $project = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($projectId);
        return $this->render('PmsCoreBundle:Receive:new.html.twig', array(
            'pri' => $pri,
            'projectName' => $project->getProjectName()

        ));
    }

    public function newAction(Request $request, $id)
    {
        $receiveItemsArr = $request->request->get('items');

        if ($request->request->has('next')){

            if ($request->getMethod() == 'POST' && !empty($receiveItemsArr)) {

                $i = 0;
                foreach ($receiveItemsArr as $project) {
                    $project = explode('/', $project);
                    $projectId[] = $project[2];
                    $vendorId[] = $project[3];
                    $buyerId[] = $project[4];
                    $poId[] = $project[5];
                    $i++;
                }
                $j = $i - 1;
                for ($i; $i > 1; $i--) {
                    if ($projectId[$j] != $projectId[($j - 1)]) {

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'You can not select two project!!!'
                        );
                        goto end;
                    }
                    if ($vendorId[$j] != $vendorId[($j - 1)]) {

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'You can not select two vendor or buyer!!!'
                        );
                        goto end;
                    }
                    if ($buyerId[$j] != $buyerId[($j - 1)]) {

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'You can not select two vendor or buyer!!!'
                        );
                        goto end;
                    }
                    if ($poId[$j] != $poId[($j - 1)]) {

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'You can not select more than one po!!!'
                        );
                        goto end;
                    }
                    $j--;
                }

                return $this->formReceive($receiveItemsArr);
            }
        }

        if ($request->request->has('amendment') && !empty($receiveItemsArr)){

            return $this->formAmendment($receiveItemsArr);
        }

        end:
        return $this->newAdd($id);
    }

    /**
     * @param $receiveItemsArr
     * @return Response
     */
    protected function formReceive($receiveItemsArr)
    {
        $receive = new Receive();

        list($purchaseRequisitionArr, $poVendorId, $poBuyerId, $itemArr, $itemUnitArr, $projectArr, $purchaseOrderArr, $quantityArr) = $this->getReceiveFormContent($receiveItemsArr, $receive);

        $form = $this->createForm(new ReceiveType(), $receive);

        return $this->render('PmsCoreBundle:Receive:form.html.twig', array(
            'receiveItems' => json_encode($receiveItemsArr, JSON_FORCE_OBJECT),
            'item' => $itemArr,
            'quantityArr' => $quantityArr,
            'poVendorId' => $poVendorId,
            'poBuyerId' => $poBuyerId,
            'itemUnit' => $itemUnitArr,
            'project' => $projectArr,
            'purchaseRequisition' => $purchaseRequisitionArr,
            'purchaseOrder' => $purchaseOrderArr,
            'form' => $form->createView(),
        ));
    }

    public function saveAction(Request $request)
    {
         $em = $this->getDoctrine()->getManager();

        $receiveItemsObj = $_POST['receiveItems'];

        $receiveItemsArr = json_decode($receiveItemsObj, true);

        $receive = new Receive();

        list($purchaseRequisitionArr, $poVendorId, $poBuyerId, $itemArr, $itemUnitArr, $projectArr, $purchaseOrderArr, $quantityArr) = $this->getReceiveFormContent($receiveItemsArr, $receive);

        $form    = $this->createForm(new ReceiveType(), $receive);

        if ($request->getMethod() == 'POST') {


            $form->handleRequest($request);

            $em->getConnection()->beginTransaction();



                if ($form->isValid()) {

                     try {

                        $user         = $this->getUser()->getId();

                        $poVendorName = $_POST['receive']['vendor'];
                        $vendor       = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->findOneBy(
                            array('vendorName' => $poVendorName)
                        );

                        $receive->setReceivedBy(
                            $this->getDoctrine()->getRepository('UserBundle:User')->findOneById($user)
                        );
                        $receive->setVendor($vendor);

                        if (empty($vendor)) {
                            $poBuyerName = $_POST['receive']['buyer'];
                            $buyer       = $this->getDoctrine()->getRepository('UserBundle:User')->findOneBy(
                                array('username' => $poBuyerName)
                            );
                            $receive->setBuyer($buyer);
                        }

                        $receive->setReceivedDate(new \DateTime());
                        $receive->setStatus('1');

                        /** @var ReceivedItem $item */
                        foreach ($receive->getReceiveItems() as $item) {
                            $purchaseOrderItem = $item->getPurchaseOrderItem();

                            $quantityOld = $purchaseOrderItem->getTotalOrderReceiveQuantity();
                            $purchaseOrderItem->setTotalOrderReceiveQuantity($quantityOld + $item->getQuantity());

                            $item->setReceive($receive);
                            $item->setPurchaseOrderItem($purchaseOrderItem);

                            $totalOrderReceiveQuantity = $item->getQuantity()
                                + $purchaseOrderItem->getPurchaseOrder()->getTotalOrderReceiveQuantity();

                            $purchaseOrderItem->getPurchaseOrder()->setTotalOrderReceiveQuantity(
                                $totalOrderReceiveQuantity);
                            $this->saveReceiveItem($item);
                        }

                        $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->create($receive);
        //                $this->updatePurchaseOderItem($receive);
                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Received Successfully'
                        );

                        $this->keepLog($receive, 1);

                        $em->getConnection()->commit();
                       return $this->redirect($this->generateUrl('receive'));

                     } catch (\Exception $e) {

                         $em->getConnection()->rollback();
                         $em->close();

                         $this->get('session')->getFlashBag()->add(
                             'notice',
                             'Your network connection not available try again!'
                         );
                         throw $e;
                         return $this->redirect($this->generateUrl('receive'));

                     }

            }


        }

        return $this->render('PmsCoreBundle:Receive:form.html.twig', array(
            'receiveItems' => json_encode($receiveItemsArr, JSON_FORCE_OBJECT),
            'item' => $itemArr,
            'quantityArr' => $quantityArr,
            'poVendorId' => $poVendorId,
            'poBuyerId' => $poBuyerId,
            'itemUnit' => $itemUnitArr,
            'project' => $projectArr,
            'purchaseRequisition' => $purchaseRequisitionArr,
            'purchaseOrder' => $purchaseOrderArr,
            'form' => $form->createView(),
        ));
    }

    public function updatePurchaseOderItem(ReceivedItem $item) {

        $purchaseOrderItem = $item->getPurchaseOrderItem();

        $purchaseOrderItem->receiveItem($item->getQuantity());
         $this->getDoctrine()
             ->getRepository('PmsCoreBundle:PurchaseOrderItem')
             ->update($purchaseOrderItem);
    }

    public function saveReceiveItem(ReceivedItem $item)
    {
        $receiveTotalItem = new TotalReceiveItem();

        $itemId     = $item->getItem()->getId();
        $project    = $item->getProject()->getId();
        $quantity   = $item->getQuantity();

        $totalReceiveItem = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->findBy(
            array('project' => $project, 'item' => $itemId)
        );

        if ($totalReceiveItem) {
            $totalExistItem = 0;
            $totalExistItem += ( $quantity + $totalReceiveItem[0]->getTotalItem() );
            $totalReceiveItem[0]->setTotalItem($totalExistItem);
            $this->getDoctrine()
                ->getRepository('PmsInventoryBundle:TotalReceiveItem')
                ->update($totalReceiveItem[0]);
        } else {
            $receiveTotalItem->setProject($this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($project));
            $receiveTotalItem->setItem($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($itemId));
            $receiveTotalItem->setTotalItem($quantity);
            $receiveTotalItem->setCreatedDate(new \DateTime(date('Y-m-d')));
            $this->getDoctrine()
                 ->getRepository('PmsInventoryBundle:TotalReceiveItem')
                 ->create($receiveTotalItem);
        }
    }

    /**
     * @param $receiveItemsArr
     * @param $receive
     * @return array
     */
    protected function getReceiveFormContent($receiveItemsArr, $receive)
    {
        foreach ($receiveItemsArr as $orderItem) {

            $orderItem = explode('/', $orderItem);
            $priId = $orderItem[0];
            $poiId = $orderItem[1];

            $purchaseRequisitionItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->find($priId);
            $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());
            $purchaseRequisitionArr[] = $purchaseRequisition->getId();

            $purchaseOrderItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->find($poiId);
            $purchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($purchaseOrderItem->getPurchaseOrder());
            $poiItem = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($purchaseOrderItem->getItem());
            $poiProject = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($purchaseOrderItem->getProject());

            $purchaseOrderVendorName = $purchaseOrder->getVendor() ? $purchaseOrder->getVendor()->getVendorName() : 0;
            $poVendorId = $purchaseOrder->getVendor() ? $purchaseOrder->getVendor()->getId() : 0;

            $purchaseOrderBuyerName = $purchaseOrder->getBuyer() ? $purchaseOrder->getBuyer()->getUsername() : 0;
            $poBuyerId = $purchaseOrder->getBuyer() ? $purchaseOrder->getBuyer()->getId() : 0;

            if ($poVendorId > 0) {
                $receive->setVendor($purchaseOrderVendorName);
            }

            if ($poBuyerId > 0) {
                $receive->setBuyer($purchaseOrderBuyerName);
            }

            $itemArr[] = $poiItem->getItemName();
            $itemUnitArr[] = $poiItem->getItemUnit();
            $projectArr[] = $poiProject->getProjectName();
            $purchaseOrderArr[] = $purchaseOrder->getId();

            $receivedItem = new ReceivedItem();
            $receivedItem->setPurchaseOrderItem($purchaseOrderItem);

            $quantityArr[] = $purchaseOrderItem->getQuantity() - $purchaseOrderItem->getTotalOrderReceiveQuantity();

            $receive->addReceiveItem($receivedItem);
        }

        return array($purchaseRequisitionArr, $poVendorId, $poBuyerId, $itemArr, $itemUnitArr, $projectArr, $purchaseOrderArr, $quantityArr);
    }

    /**
     * @param $orderItems
     * @param $purchaseOrder
     * @return array
     */
    protected function getAmendmentFormContent1($orderItems, $purchaseOrder)
    {
        $orderSplit = explode('/', $orderItems[0]);
        $poId = $orderSplit[5];

        $j = 0;
        foreach ($orderItems as $orderItem) {

            $idList = explode('/', $orderItem);

            $priId = $idList[0];
            $poiId = $idList[1];

            $purchaseOrderItem = new PurchaseOrderItem();

            $purchaseRequisitionItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->find($priId);
            $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());
            $purchaseRequisitionArr[] = $purchaseRequisition->getId();

            $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($purchaseRequisitionItem->getItem());
            $purchaseRequisitionNumber = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());
            $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());
            $project = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($purchaseRequisition->getProject());


            $purchaseOrderItemQuantity = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->find($poiId);


            $getQuantity = $purchaseOrderItemQuantity->getQuantity();

            $getTotalOrderReceiveQuantity = $purchaseOrderItemQuantity->getTotalOrderReceiveQuantity();

            if($getTotalOrderReceiveQuantity == null || $getTotalOrderReceiveQuantity == ''){
                $getTotalOrderReceiveQuantity = 0;
            }

            $purchaseOrderItemQuantity->setPurchaseRequisitionItem($purchaseRequisitionItem);


            $quantityRest = $getQuantity - $getTotalOrderReceiveQuantity;

            $purchaseOrder->addPurchaseOrderItem($purchaseOrderItem);


            $itemNameArr[] = $item->getItemName();
            $quantityArr[] = $quantityRest;
            $itemIdArr[] = $item->getId();
            $itemUnitArr[] = $item->getItemUnit();
            $projectNameArr[] = $project->getProjectName();
            $projectHeadCell[] = $project->getProjectHead()->getProfile()->getCellphone();
            $purchaseRequisitionNumberArr[] = $purchaseRequisitionNumber->getId();
            $projectAddressArr[] = $project->getAddress();

            $j = $j + 1;
        }

        return array($itemNameArr, $quantityArr, $itemIdArr, $itemUnitArr, $projectNameArr, $projectHeadCell, $purchaseRequisitionNumberArr, $projectAddressArr);
    }
    protected function getAmendmentFormContent($orderItems, $purchaseOrder)
    {
        $orderSplit = explode('/', $orderItems[0]);
        $poId = $orderSplit[5];

        $j = 0;
        foreach ($orderItems as $orderItem) {

            $idList = explode('/', $orderItem);

            $priId = $idList[0];
            $poiId = $idList[1];

            $purchaseOrderItem = new PurchaseOrderItem();
            //  $purchaseOrderItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->find($poiId);
            $purchaseRequisitionItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->find($priId);
            $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($purchaseRequisitionItem->getItem());
            $purchaseRequisitionNumber = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());
            $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());
            $project = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($purchaseRequisition->getProject());

          //  $purchaseOrderItemQuantity = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findOneBy(array('purchaseOrder'=> $poId, 'item'=> $item,'project'=>$project));
            $purchaseOrderItemQuantity = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->find($poiId);

            $getQuantity = $purchaseOrderItemQuantity->getQuantity();

            $getTotalOrderReceiveQuantity = $purchaseOrderItemQuantity->getTotalOrderReceiveQuantity();
            if($getTotalOrderReceiveQuantity == null || $getTotalOrderReceiveQuantity == ''){
                $getTotalOrderReceiveQuantity = 0;
            }

            $purchaseOrderItem->setPurchaseRequisitionItem($purchaseRequisitionItem);

            $quantityRest = $getQuantity - $getTotalOrderReceiveQuantity;
            $purchaseOrder->addPurchaseOrderItem($purchaseOrderItem);

            $itemNameArr[] = $item->getItemName();
            $quantityArr[] = $quantityRest;
            $itemIdArr[] = $item->getId();
            $itemUnitArr[] = $item->getItemUnit();
            $projectNameArr[] = $project->getProjectName();
            $projectHeadCell[] = $project->getProjectHead()->getProfile()->getCellphone();
            $purchaseRequisitionNumberArr[] = $purchaseRequisitionNumber->getId();
            $projectAddressArr[] = $project->getAddress();

            $j = $j + 1;
        }


        return array($itemNameArr, $quantityArr, $itemIdArr, $itemUnitArr, $projectNameArr, $projectHeadCell, $purchaseRequisitionNumberArr, $projectAddressArr);
    }

    public function approveOneAjaxAction(Request $request)
    {
        $id = $request->request->get('receiveId');

        if($id){
            $receive = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->find($id);

            $receive->setStatus(3);
            $receive->setClosedDate(new \DateTime());

            $receive->setApproveStatus(1);
            $receive->setApprovedOne($this->getUser());
            $receive->setApprovedOneDate(new \DateTime());

            $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->update($receive);

            $this->keepLog($receive, 3);
            $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->createReceive($receive);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function verifiedBillCheckAction(Request $request)
    {
        $paymentData = $request->request->all();

        $billNumber = $paymentData['billNumber'];
        $billDate = $paymentData['billNumber'];
        $vendorId = $paymentData['vendorId'];
        $buyerId = $paymentData['buyerId'];

           if($buyerId) {
               $billCheck = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')
                   ->findBy(array('buyer' => $buyerId,
                   'billNumber' => $billNumber
               ));

           }else{
               $billCheck = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')
                   ->findBy(array('vendor' => $vendorId,
                   'billNumber' => $billNumber
               ));
           }

        if(empty($billCheck)){
             return new Response();
        } else {
            return new  Response('Duplicate');
        }
    }
    public function poiItemCloseAction(Request $request, PurchaseOrderItem $purchaseOrderItem)
    {

        if($request->request->all())
        {

            $purchaseOrderItemClose = new PurchaseOrderItemClose();
            $purchaseOrderItemClose->setClosedBy($this->getUser());
            $purchaseOrderItemClose->setClosedDate(new \DateTime());
            $purchaseOrderItemClose->setQuantity($purchaseOrderItem->getQuantity() - $purchaseOrderItem->getTotalOrderReceiveQuantity());
            $purchaseOrderItemClose->setPurchaseOrderItem($purchaseOrderItem);

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItemClose')->create($purchaseOrderItemClose);

            $totalPoItemQuantity = $purchaseOrderItem->getTotalOrderReceiveQuantity() == 0 ? 0 : $purchaseOrderItem->getTotalOrderReceiveQuantity();
            $totalPoItemPrice = $purchaseOrderItem->getTotalOrderReceiveQuantity() == 0 ? 0 : $purchaseOrderItem->getPrice() * $purchaseOrderItem->getTotalOrderReceiveQuantity();
            $poQuantity= ($purchaseOrderItem->getPurchaseOrder()->getTotalOrderItemQuantity() + $purchaseOrderItem->getTotalOrderReceiveQuantity()) - $purchaseOrderItem->getQuantity();
            $poSubTotalPrice = ( $purchaseOrderItem->getPurchaseOrder()->getSubTotal() + $purchaseOrderItem->getPrice() * $purchaseOrderItem->getTotalOrderReceiveQuantity() ) - $purchaseOrderItem->getAmount();
            $poNetTotalPrice = ( $purchaseOrderItem->getPurchaseOrder()->getNetTotal() + $purchaseOrderItem->getPrice() * $purchaseOrderItem->getTotalOrderReceiveQuantity() ) - $purchaseOrderItem->getAmount();

            $purchaseOrderItem->setStatus(2);
            $purchaseOrderItem->setRemark($request->request->all()['close-remark']);

            $purchaseOrderItem->setQuantity($totalPoItemQuantity);
            $purchaseOrderItem->setAmount($totalPoItemPrice);
            $purchaseOrderItem->getPurchaseOrder()->setSubTotal($poSubTotalPrice);
            $purchaseOrderItem->getPurchaseOrder()->setNetTotal($poNetTotalPrice);
            $purchaseOrderItem->getPurchaseOrder()->setTotalOrderItemQuantity($poQuantity);
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->update($purchaseOrderItem);

            $purchaseOrderItem->getPurchaseOrder()->getTotalOrderItemQuantity();

            if($purchaseOrderItem->getPurchaseOrder()->getTotalOrderItemQuantity() == 0){
                $purchaseOrderItem->getPurchaseOrder()->setStatus(6);
                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->update($purchaseOrderItem);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase order item successfully close'
            );
            return $this->redirect($this->generateUrl('receive'));
        }
        else {
            return $this->render('PmsCoreBundle:Receive:purchaseOrderReceiveItemClose.html.twig',array(
                'purchaseOrderItemId' =>$purchaseOrderItem->getId()
            ));
        }

    }



    /**
     * @param $invoice
     * @param $key
     * @param $challan
     * @param $receive
     * @param $billDate
     * @param $billNumber
     */
    private function receiveItemCreateForReadyPayment($invoice, $key, $challan, $receive, $billDate, $billNumber)
    {
        $invoiceObj = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($invoice[$key]);
        $challanObj = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($challan[$key]);

        $receive->setInvoice($invoiceObj);
        $receive->setCalan($challanObj);
        $receive->setBillDate($billDate);
        $receive->setBillNumber($billNumber);
        $receive->setStatus(3);
        $receive->setClosedDate(new \DateTime());
        $receive->setApproveStatus(1);
        $receive->setApprovedOne($this->getUser());
        $receive->setApprovedOneDate(new \DateTime());

        $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->update($receive);

        $this->keepLog($receive, 3);
        $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->createReceive($receive);
    }
    /**
     * @param $invoice
     * @param $key
     * @param $challan
     * @param $receive
     * @param $billDate
     * @param $billNumber
     */
    private function receiveItemUpdateForReadyPayment($invoice, $key, $challan, $receive, $billDate, $billNumber)
    {

        $invoiceObj = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($invoice[$key]);
        $challanObj = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($challan[$key]);

        $receive->setInvoice($invoiceObj);
        $receive->setCalan($challanObj);
        $receive->setBillDate($billDate);
        $receive->setBillNumber($billNumber);
        $receive->setStatus(3);
        $receive->setClosedDate(new \DateTime());
        $receive->setApproveStatus(1);
        $receive->setApprovedOne($this->getUser());
        $receive->setApprovedOneDate(new \DateTime());
        $receive->setSendBackStatus(NULL);

        $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->update($receive);

        $this->keepLog($receive, 3);
        $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->updateReceiveForReadyPayment($receive);
    }

    public function invoiceBillAutoCompleteAction(Request $request){

        $itemName = $request->query->get('q', null);
        if ($itemName) {
            $invoiceQueries = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')
                ->invoiceBillAutoCompleteForReceive($itemName,$this->getUser());
            foreach($invoiceQueries as $invoiceQuery){
                $itemQuery[] = array(
                    'id' => $invoiceQuery['id'],
                    'text' => $invoiceQuery['prId'].'--'.$invoiceQuery['billNumber'].'--'.$invoiceQuery['amount'].'--'.$invoiceQuery['username']
                );
            }
        }
        return new JsonResponse($itemQuery);
    }
    public function invoiceBillAutoCompleteModalAction(Request $request){

        $itemName = $request->query->get('q', null);
        if ($itemName) {
            $invoiceQueries = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')
                ->invoiceBillAutoCompleteForReceiveModal($itemName,$this->getUser());
            foreach($invoiceQueries as $invoiceQuery){
                $itemQuery[] = array(
                    'id' => $invoiceQuery['id'],
                    'text' => $invoiceQuery['prId'].'--'.$invoiceQuery['billNumber'].'--'.$invoiceQuery['amount'].'--'.$invoiceQuery['username']
                );
            }
        }
        return new JsonResponse($itemQuery);
    }
    public function challanAutoCompleteAction(Request $request){


        $itemName = $request->query->get('q', null);
        if ($itemName) {

            $invoiceQueries = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')
                ->calanAutoCompleteForReceive($itemName,$this->getUser());

            foreach($invoiceQueries as $invoiceQuery){
                $itemQuery[] = array(
                    'id' => $invoiceQuery['id'],
                    'text' => $invoiceQuery['prId'].'--'.$invoiceQuery['billNumber'].'--'.$invoiceQuery['amount'].'--'.$invoiceQuery['username']
                );
            }
        }
        return new JsonResponse($itemQuery);
    }
    public function challanAutoCompleteModalAction(Request $request){


        $itemName = $request->query->get('q', null);
        if ($itemName) {

            $invoiceQueries = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')
                ->calanAutoCompleteForReceiveModal($itemName,$this->getUser());

            foreach($invoiceQueries as $invoiceQuery){
                $itemQuery[] = array(
                    'id' => $invoiceQuery['id'],
                    'text' => $invoiceQuery['prId'].'--'.$invoiceQuery['billNumber'].'--'.$invoiceQuery['amount'].'--'.$invoiceQuery['username']
                );
            }
        }
        return new JsonResponse($itemQuery);
    }
}
