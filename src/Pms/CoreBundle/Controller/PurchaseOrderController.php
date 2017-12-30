<?php

namespace Pms\CoreBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\CoreBundle\Entity\Log\PoLog;
use Pms\CoreBundle\Entity\PurchaseOrder;
use Pms\CoreBundle\Entity\PurchaseOrderComment;
use Pms\CoreBundle\Entity\PurchaseOrderItem;
use Pms\CoreBundle\Entity\PurchaseRequisitionItem;
use Pms\CoreBundle\Entity\PurchaseRequisitionItemCloseInfo;
use Pms\CoreBundle\Entity\ReadyForPayment;
use Pms\CoreBundle\Form\PurchaseOrderType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Pms\CoreBundle\Form\SearchForm\PurchaseOrderSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Snappy\Pdf;

/**
 * Purchase Order controller.
 *
 */
class PurchaseOrderController extends Controller
{
    public function indexAction(Request $request, $status = 'all')
    {
        if(!empty($request->query->all())){
            $month = $request->query->all();
            if(!isset($month['month'])) {
                $month['month'] = '';
            }
        }else{
            $month = 1;
        }


        $purchaseOrderRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder');

        $form = new PurchaseOrderSearchType();

        $data = $request->query->get($form->getName());

        $formSearch = $this->createForm($form, $data);
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if($mobileDetector->isMobile() OR $mobileDetector->isTablet() ){
            if(empty($data)){
                $status = $status == '' ? $status : 'open';
            }
            $purchaseOrders = $this->paginate($purchaseOrderRepository->getPurchaseOrderSearch($this->getUser(), $data,$status,$month));
            return $this->render('PmsCoreBundle:PurchaseOrder:homeMobile.html.twig', array(
                'purchaseOrders' => $purchaseOrders,
                'formSearch' => $formSearch->createView(),
                'status' => $status,
            ));
        }
        $purchaseOrders = $this->paginate($purchaseOrderRepository->getPurchaseOrderSearch($this->getUser(), $data, $status,$month));
        return $this->render('PmsCoreBundle:PurchaseOrder:home.html.twig', array(
            'purchaseOrders' => $purchaseOrders,
            'formSearch' => $formSearch->createView(),
            'status' => $status,
        ));
    }

    public function editAction(Request $request, PurchaseOrder $purchaseOrder)
    {
        foreach ($purchaseOrder->getPurchaseOrderItems() as $orderItem) {
            $itemNameArr[] = $orderItem->getItem()->getItemName();
            $itemIdArr[] =  $orderItem->getItem()->getId();
            $itemQtyArr[] =  $orderItem->getQuantity();
            $itemUnitArr[] = $orderItem->getItem()->getItemUnit();
            $projectNameArr[] = $orderItem->getProject()->getProjectName();
            $projectHeadCell[] = $orderItem->getProject()->getProjectHead()->getProfile()->getCellphone();
            $purchaseRequisitionNumberArr[] = $orderItem->getPurchaseRequisitionItem()->getPurchaseRequisition()->getId();
            $projectAddressArr[] = $orderItem->getProject()->getAddress();
        }

        $form = $this->createForm(new PurchaseOrderType(), $purchaseOrder);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $purchaseOrder->setUpdatedBy($this->getUser());
                $purchaseOrder->setUpdatedDate(new \DateTime());

                $i = 0;
                /** @var PurchaseOrderItem $item */
                foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {

                    $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();

                    $purchaseRequisitionItem->setPurchaseOrderQuantity(
                        ($purchaseRequisitionItem->getPurchaseOrderQuantity() - $itemQtyArr[$i])
                        + $_POST["purchaseorder"]["purchaseOrderItems"][$i]["quantity"]
                    );

                    $purchaseOrder->setTotalOrderItemQuantity(
                        ($purchaseOrder->getTotalOrderItemQuantity() - $itemQtyArr[$i])
                        + $_POST["purchaseorder"]["purchaseOrderItems"][$i]["quantity"]
                    );

                    $purchaseRequisitionItem->getPurchaseRequisition()->setTotalOrderItemQuantity(
                        ($purchaseRequisitionItem->getPurchaseRequisition()->getTotalOrderItemQuantity() - $itemQtyArr[$i])
                        + $_POST["purchaseorder"]["purchaseOrderItems"][$i]["quantity"]
                    );

                    $i = $i + 1;
                }

                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);

                if( $purchaseOrder->getPaymentType() == 'Advance payment' ){
                    $this->editReadyForPayment($purchaseOrder);
                }

                return $this->redirect($this->generateUrl('purchase_order'));
            }
        }

        return $this->render('PmsCoreBundle:PurchaseOrder:formEdit.html.twig', array(
            'item' => $itemNameArr,
            'itemId' => $itemIdArr,
            'project' => $projectNameArr,
            'projectHeadCell' => $projectHeadCell,
            'number' => $purchaseRequisitionNumberArr,
            'address' => $projectAddressArr,
            'itemUnit' => $itemUnitArr,
            'form' => $form->createView(),
        ));
    }

    public function editReadyForPayment($purchaseOrder)
    {
        $readyForPayment = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                                ->findOneBy(array(
                                    'purchaseOrder' => $purchaseOrder,
                                    'grn'           => null
                                ));

        if($readyForPayment) {
            if ($purchaseOrder->getVendor() != null) {
                $readyForPayment->setVendor($purchaseOrder->getVendor());
            } elseif ($purchaseOrder->getBuyer() != null) {
                $readyForPayment->setVendor($purchaseOrder->getBuyer());
            }

            $advancePaymentAmount = $purchaseOrder->getAdvancePaymentAmount();
            $poAmount = $purchaseOrder->getNetTotal();

            $readyForPayment->setPoAmount($poAmount);
            $readyForPayment->setAdvancePaymentAmount($advancePaymentAmount);
            $readyForPayment->setPurchaseOrder($purchaseOrder);
            $readyForPayment->setRequestDate(new \DateTime());
            $readyForPayment->setRequestBy($this->getUser());
            $readyForPayment->setStatus(1);
            $readyForPayment->setExtStatus(1);

            $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                 ->update($readyForPayment);
        }
    }

    private function itemList()
    {
        $purchaseRequisitionItem = $this->getDoctrine()
                                        ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                                        ->itemListForPurchaseOrder($this->getUser());

        /*if(in_array('PR_ITEM_CLOSE_HEAD_OFFICE',$this->getUser()->getRoles())){
            var_dump($this->getUser()->getRoles());die;
        }die('not ok');*/
        return $this->render('PmsCoreBundle:PurchaseOrder:new.html.twig', array(
            'pri' => $purchaseRequisitionItem,
        ));
    }

    public function newAction(Request $request)
    {
        $orderItems = $request->request->get('items');

        if ($request->getMethod() == 'POST' && !empty($orderItems)) {
            return $this->form($orderItems);
        }

        return $this->itemList();
    }

    public function attachmentViewAction(Request $request, PurchaseOrder $purchaseOrder, $index)
    {
        if(null !== $response = $this->checkAttachFileByIndex($purchaseOrder, $index)) {
            return $response;
        }

        if($index == 1){
            $path = $purchaseOrder->getPath();
        }elseif($index == 2){
            $path = $purchaseOrder->getPathTwo();
        }elseif($index == 3){
            $path = $purchaseOrder->getPathThree();
        }elseif($index == 4){
            $path = $purchaseOrder->getPathAmendment();
        }

        return $this->render('PmsCoreBundle:PurchaseOrder:viewer.html.twig', array(
            'po' => $purchaseOrder,
            'path' => $path,
        ));
    }

    private function checkAttachFileByIndex(PurchaseOrder $purchaseOrder, $index)
    {
        if (null == $fileName = $purchaseOrder->getAbsolutePathByIndex($index)) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function selectPurchaseOrderPaymentFromType($request){

        $paymentFrom = $request->request->all()['purchaseorder']['paymentFrom'];
        if($paymentFrom == 'local-office'){
            return 1;
        } else {
            return 2;
        }
    }

    public function saveAction(Request $request)
    {

        $orderItemsObj = $_POST['orderItems'];
        $orderItems = json_decode($orderItemsObj, true);

        $purchaseOrder = new PurchaseOrder();

        list($itemNameArr, $quantityArr, $itemIdArr, $itemUnitArr, $projectNameArr, $projectHeadCell, $purchaseRequisitionNumberArr, $projectAddressArr) = $this->getOrderFormContent($orderItems, $purchaseOrder);

        $form = $this->createForm(new PurchaseOrderType(), $purchaseOrder);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $purchaseOrder->setCreatedBy($this->getUser());
                $purchaseOrder->setCreatedDate(new \DateTime());
                $purchaseOrder->setStatus(1);
                $purchaseOrder->setPaymentFrom($this->selectPurchaseOrderPaymentFromType($request));
                $purchaseOrder->setApproveStatus(0);
                $purchaseOrder->setTotalOrderItem(0);
                $purchaseOrder->setTotalOrderItemQuantity(0);

                /** @var PurchaseOrderItem $item */
                foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {

                    $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();

                    $purchaseRequisitionItem->setPurchaseOrderQuantity(
                        $purchaseRequisitionItem->getPurchaseOrderQuantity() + $item->getQuantity()
                    );

                    $item->setPurchaseRequisitionItem($purchaseRequisitionItem);
                    $item->setPurchaseOrder($purchaseOrder);
                    $item->setStatus(1);
                    $item->setAmendment(0);
                    $item->setAmendmentRef(0);
                    $item->setAmendmentQuantity(0);

                    $purchaseOrder->setTotalOrderItem(1 + $purchaseOrder->getTotalOrderItem());
                    $purchaseOrder->setTotalOrderItemQuantity(
                        $item->getQuantity() + $purchaseOrder->getTotalOrderItemQuantity());

                    $totalOrderItemQuantity = $item->getQuantity()
                        + $purchaseRequisitionItem->getPurchaseRequisition()->getTotalOrderItemQuantity();

                        $purchaseRequisitionItem->getPurchaseRequisition()->setTotalOrderItemQuantity(
                            $totalOrderItemQuantity);
//                    $item->setRemainingQuantity($item->getQuantity());
                }

                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->create($purchaseOrder);



                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Purchase Order Successfully Add'
                );

//                $this->sendEmail($purchaseOrder, 'role');
                return $this->redirect($this->generateUrl('purchase_order'));
            }
        }

        return $this->render('PmsCoreBundle:PurchaseOrder:form.html.twig', array(
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



    public function detailsAction(PurchaseOrder $purchaseOrder)
    {

       /* $findPurchaseOrderIds = $this->getDoctrine()
                                     ->getRepository('PmsCoreBundle:PurchaseOrderItem')
                                     ->findPurchaseOrderId($purchaseOrder->getId());*/

        $purchaseOrderItems = $this->getDataFormPoForDetails($purchaseOrder);

        $purchaseOrderLowestPrice = $this->getDoctrine()
                                         ->getRepository('PmsCoreBundle:PurchaseOrderItem')
                                         ->getLowestPriceAmongProject($purchaseOrder);

        $poiList = array();
        foreach ($purchaseOrderItems as $id => $poiInfo) {

            $itemDetails = array(
                'itemName' => $poiInfo?$poiInfo['itemName']:'',
                'lastPurchaseType' =>$poiInfo?$poiInfo['purchaseType']:'',
                'lastPoRef' =>$poiInfo?$poiInfo['poId']:'',
                'lastProjectName' =>$poiInfo?$poiInfo['projectName']:'',
                'LastPrice' =>$poiInfo?$poiInfo['price']:'',
                'lowestPurchaseType' => !empty($purchaseOrderLowestPrice[$id]) ? $purchaseOrderLowestPrice[$id]['purchaseType']:'',
                'poRef' => !empty($purchaseOrderLowestPrice[$id]) ? $purchaseOrderLowestPrice[$id]['poId']:'',
                'lowestPrice' => !empty($purchaseOrderLowestPrice[$id]) ? $purchaseOrderLowestPrice[$id]['price']:'',
                'lowestProjectName' => !empty($purchaseOrderLowestPrice[$id]) ? $purchaseOrderLowestPrice[$id]['projectName']:'',

            );

            $poiList[$id] = $itemDetails;

        }
        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')
                                         ->findInfoForPoHistory($purchaseOrder->getId());

        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if($mobileDetector->isMobile() OR $mobileDetector->isTablet() ) {

            return $this->render('PmsCoreBundle:PurchaseOrder:detailsForMobile.html.twig', array(
                'po' => $purchaseOrder,
                'poi' => $poiList,
                'pri' => $purchaseRequisitionItems,
            ));

        }
//var_dump($purchaseOrder);die;
            return $this->render('PmsCoreBundle:PurchaseOrder:details.html.twig', array(
            'po' => $purchaseOrder,
            'poi' => $poiList,
            'pri' => $purchaseRequisitionItems,
        ));
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return array
     */
    protected function getDataFormPoForDetails(PurchaseOrder $purchaseOrder)
    {

        foreach($purchaseOrder->getPurchaseOrderItems() as $poi ) {

            $query = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')

                ->createQueryBuilder('poi')
                ->select('i.itemName')
                ->addSelect('pon.name as purchaseType')
                ->addSelect('po.refNo as poRef')
                ->addSelect('po.id as poId')
                ->addSelect('p.projectName')
                ->addSelect('poi.price')
                ->addSelect('poi.id as poiId')
                ->join('poi.item', 'i')
                ->join('poi.purchaseOrder', 'po')
                ->join('poi.project', 'p')
                ->join('po.poNonpo', 'pon')
                ->where("i.id =".$poi->getItem()->getId())
                ->andWhere("p.id =".$poi->getProject()->getId())
                ->orderBy('po.approvedThreeDate', 'DESC');

                $find = $this->getDoctrine()
                            ->getRepository('PmsCoreBundle:PurchaseOrderItem')
                             ->findBy(array('item'=>$poi->getItem()->getId(),'project'=>$poi->getProject()->getId()));
               $total = count($find);

                if( $total > 1) {

                    $query->andWhere("po.id != :id ")->setParameter('id', $purchaseOrder->getId()) ;
                }

            $query->setMaxResults(1);

             $purchaseOrderItems[$poi->getItem()->getId()] = $query->getQuery()->getOneOrNullResult();

        }

        return $purchaseOrderItems;
    }

    public function lastDetailsAction($id)
    {
        $poiQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->createQueryBuilder('poi')
            ->where("i.id = :id")
            ->setParameter('id', $id)
            ->join('poi.item', 'i')
            ->orderBy('poi.id', 'DESC')
            ->setMaxResults('1');
        $purchaseOrderItems = $poiQuery->getQuery()->getResult();

        $poiQueryFindPurchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->createQueryBuilder('poi')
            ->select('po.id')
            ->where("i.id = :id")
            ->setParameter('id', $id)
            ->join('poi.item', 'i')
            ->join('poi.purchaseOrder', 'po')
            ->orderBy('poi.id', 'DESC')
            ->setMaxResults('1');
        $purchaseOrder = $poiQueryFindPurchaseOrder->getQuery()->getResult();

        $poId = $purchaseOrder[0]["id"];
        $purchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($poId);

        return $this->render('PmsCoreBundle:PurchaseOrder:itemDetails.html.twig', array(
            'poi' => $purchaseOrderItems,
            'po' => $purchaseOrder,
        ));
    }

    public function detailsOthersAction(PurchaseOrder $purchaseOrder)
    {
        $findPurchaseOrderIds = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findPurchaseOrderId($purchaseOrder->getId());
        $purchaseOrderItems = $this->getDataFormPoForDetails($purchaseOrder, $findPurchaseOrderIds);
        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')
                                         ->findInfoForPoHistory($purchaseOrder->getId());

        return $this->render('PmsCoreBundle:PurchaseOrder:detailsOthers.html.twig', array(
            'po' => $purchaseOrder,
            'poi' => $purchaseOrderItems,
            'pri' => $purchaseRequisitionItems,
        ));
    }
    public function detailsForMobileAction(PurchaseOrder $purchaseOrder)
    {
        $findPurchaseOrderIds = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findPurchaseOrderId($purchaseOrder->getId());
        $purchaseOrderItems = $this->getDataFormPoForDetails($purchaseOrder, $findPurchaseOrderIds);
        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')
                                         ->findInfoForPoHistory($purchaseOrder->getId());

        return $this->render('PmsCoreBundle:PurchaseOrder:detailsForMobile.html.twig', array(
            'po' => $purchaseOrder,
            'poi' => $purchaseOrderItems,
            'pri' => $purchaseRequisitionItems,
        ));
    }

    public function printAction(PurchaseOrder $purchaseOrder)
    {
        $termsAndConditions = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->findBySector($purchaseOrder->getPoNonpo());

        return $this->render('PmsCoreBundle:PurchaseOrder:print.html.twig', array(
            'po' => $purchaseOrder,
            'termsAndConditions' => $termsAndConditions,
        ));
    }

    public function pdfAction(PurchaseOrder $purchaseOrder)
    {

        $termsAndConditions = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->findBy(array('sector'=>$purchaseOrder->getPoNonpo(),'status'=>1));

        $html = $this->renderView(
            'PmsCoreBundle:PurchaseOrder:pdf.html.twig', array(
                'po' => $purchaseOrder,
                'termsAndConditions' => $termsAndConditions,
            )
        );
        $wkhtmltopdfPath = 'xvfb-run --server-args="-screen 0, 1280x1024x24" /usr/bin/wkhtmltopdf --use-xserver';
        $snappy          = new Pdf($wkhtmltopdfPath);
        $pdf             = $snappy->getOutputFromHtml($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="purchaseOrder.pdf"');
        echo $pdf;

        return new Response('');
    }

    public function refreshAction(PurchaseOrder $purchaseOrder)
    {
        $findPurchaseOrderIds = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findPurchaseOrderId($purchaseOrder->getId());
        $purchaseOrderItems = $this->getDataFormPoForDetails($purchaseOrder, $findPurchaseOrderIds);
        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')
                                         ->findInfoForPoHistory($purchaseOrder->getId());

        return $this->render('PmsCoreBundle:PurchaseOrder:refresh.html.twig', array(
            'po' => $purchaseOrder,
            'poi' => $purchaseOrderItems,
            'pri' => $purchaseRequisitionItems,
        ));
    }

    public function approveOneAjaxAction(Request $request)
    {
        $id = $request->request->get('reqId');

        if($id){
            $purchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($id);

            $purchaseOrder->setApproveStatus(1);
            $purchaseOrder->setApprovedOne($this->getUser());
            $purchaseOrder->setApprovedOneDate(new \DateTime());

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {
                $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();
                $purchaseRequisitionItem->setPoApprovalStatus(1);
                $item->setApprovalStatus(1);
            }

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function approveTwoAjaxAction(Request $request)
    {
        $id = $request->request->get('reqId');

        if($id){
            $purchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($id);

            $purchaseOrder->setApproveStatus(2);
            $purchaseOrder->setApprovedTwo($this->getUser());
            $purchaseOrder->setApprovedTwoDate(new \DateTime());

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {
                $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();
                $purchaseRequisitionItem->setPoApprovalStatus(2);
                $item->setApprovalStatus(2);
            }

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function approveThreeAjaxAction(Request $request)
    {
        $id = $request->request->get('reqId');

        if($id){
            $purchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($id);

            $purchaseOrder->setApproveStatus(3);
            $purchaseOrder->setApprovedThree($this->getUser());
            $purchaseOrder->setApprovedThreeDate(new \DateTime());

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {
                $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();
                $purchaseRequisitionItem->setPoApprovalStatus(3);
                $item->setApprovalStatus(3);
            }

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);
            if( $purchaseOrder->getPaymentType() == 'Advance payment' && $purchaseOrder->getAdvancePaymentAmount() > 0 ){
                $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->create($purchaseOrder);
            }
            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function approveOneAction(PurchaseOrder $purchaseOrder)
    {
        if($purchaseOrder->getApproveStatus() == 0){

            $purchaseOrder->setApproveStatus(1);
            $purchaseOrder->setApprovedOne($this->getUser());
            $purchaseOrder->setApprovedOneDate(new \DateTime());

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {
                $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();
                $purchaseRequisitionItem->setPoApprovalStatus(1);
                $item->setApprovalStatus(1);
            }

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Order Successfully Approved'
            );
        }
        return $this->redirect($this->generateUrl('purchase_order'));
    }

    public function approveTwoAction(PurchaseOrder $purchaseOrder)
    {
        if($purchaseOrder->getApproveStatus() == 1) {

            $purchaseOrder->setApproveStatus(2);
            $purchaseOrder->setApprovedTwo($this->getUser());
            $purchaseOrder->setApprovedTwoDate(new \DateTime());

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {
                $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();
                $purchaseRequisitionItem->setPoApprovalStatus(2);
                $item->setApprovalStatus(2);
            }

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Order Successfully Approved'
            );
        }
        return $this->redirect($this->generateUrl('purchase_order'));
    }

    public function approveThreeAction(PurchaseOrder $purchaseOrder)
    {
        if($purchaseOrder->getApproveStatus() == 2) {

            $purchaseOrder->setApproveStatus(3);
            $purchaseOrder->setApprovedThree($this->getUser());
            $purchaseOrder->setApprovedThreeDate(new \DateTime());

            /** @var PurchaseOrderItem $item */
            foreach ($purchaseOrder->getPurchaseOrderItems() as $item) {
                $purchaseRequisitionItem = $item->getPurchaseRequisitionItem();
                $purchaseRequisitionItem->setPoApprovalStatus(3);
                $item->setApprovalStatus(3);
            }

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);
            if ($purchaseOrder->getPaymentType() == 'Advance payment' && $purchaseOrder->getAdvancePaymentAmount(
                ) > 0
            ) {
                $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->create($purchaseOrder);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Order Successfully Approved'
            );
        }
        return $this->redirect($this->generateUrl('purchase_order'));
    }

    public function holdAction(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->setStatus(5);
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);

        $this->keepLog($purchaseOrder, 5);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Purchase Order Successfully Hold'
        );

        return $this->redirect($this->generateUrl('purchase_order'));
    }

    public function openAction(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->setStatus(1);
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);

        $this->keepLog($purchaseOrder, 1);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Purchase Order Successfully Open'
        );

        return $this->redirect($this->generateUrl('purchase_order'));
    }

    public function cancelAction(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->setStatus(6);
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->update($purchaseOrder);

        $this->keepLog($purchaseOrder, 6);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Purchase Order Successfully Cancel'
        );

        return $this->redirect($this->generateUrl('purchase_order'));
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param $status
     */
    protected function keepLog(PurchaseOrder $purchaseOrder, $status)
    {
        $poLog = new PoLog();

        $poLog->setPurchaseOrder($purchaseOrder);
        $poLog->setCreatedBy($this->getUser());
        $poLog->setStatus($status);
        $this->getDoctrine()->getRepository('PmsCoreBundle:Log\PoLog')->create($poLog);
    }

    public function itemCloseAction(Request $request, PurchaseRequisitionItem $purchaseRequisitionItem)
    {
        if($request->request->all())
        {

            $purchaseRequisitionItemCloseInfo = new PurchaseRequisitionItemCloseInfo();
            $purchaseRequisitionItemCloseInfo->setClosedBy($this->getUser());
            $purchaseRequisitionItemCloseInfo->setClosedDate(new \DateTime());
            $purchaseRequisitionItemCloseInfo->setQuantity($purchaseRequisitionItem->getQuantity() - $purchaseRequisitionItem->getPurchaseOrderQuantity());
            $purchaseRequisitionItemCloseInfo->setPurchaseRequisitionItem($purchaseRequisitionItem);

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItemCloseInfo')->create($purchaseRequisitionItemCloseInfo);

            $updateTotalItemQty = ($purchaseRequisitionItem->getPurchaseRequisition()->getTotalRequisitionItemQuantity()-
                $purchaseRequisitionItem->getQuantity())+$purchaseRequisitionItem->getPurchaseOrderQuantity();

            $purchaseRequisitionItem->setStatus(2);
            $purchaseRequisitionItem->setCloseRemark($request->request->all()['close-remark']);

            $purchaseRequisitionItem->setQuantity($purchaseRequisitionItem->getPurchaseOrderQuantity());

            $purchaseRequisitionItem->getPurchaseRequisition()->setTotalRequisitionItemQuantity($updateTotalItemQty);
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->update($purchaseRequisitionItem);

            $purchaseRequisitionItem->getPurchaseRequisition()->getTotalRequisitionItemQuantity();

            if($purchaseRequisitionItem->getPurchaseRequisition()->getTotalRequisitionItemQuantity() == 0){

                // status cancel = 6
                $purchaseRequisitionItem->getPurchaseRequisition()->setStatus(6);
                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->update($purchaseRequisitionItem);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Order Successfully item close'
            );
             return $this->redirect($this->generateUrl('purchase_order'));
        }
        else {
            return $this->render('PmsCoreBundle:PurchaseOrder:purchaseOrderitemClose.html.twig',array(
                'purchaseRequisitionId' =>$purchaseRequisitionItem->getId()
            ));
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
            25/*limit per page*/
        );

        return $value;
    }



    /**
     * @param $orderItems
     * @return Response
     */
    protected function form($orderItems)
    {
        $purchaseOrder = new PurchaseOrder();

        list($itemNameArr, $quantityArr, $itemIdArr, $itemUnitArr, $projectNameArr, $projectHeadCell, $purchaseRequisitionNumberArr, $projectAddressArr) = $this->getOrderFormContent($orderItems, $purchaseOrder);

        $form = $this->createForm(new PurchaseOrderType(), $purchaseOrder);

        return $this->render('PmsCoreBundle:PurchaseOrder:form.html.twig', array(
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

    public function findTermsAndConditionAction(Request $request)
    {
        $poNonpo = $request->request->get('poNonpo');

        $termsAndConditions = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->findBy(array('sector' => $poNonpo, 'status' => 1));
        $tocArr = array();
        foreach ($termsAndConditions as $toc) {
            $tocArr[] = " <div class='tocA'' style='width: 100%;border: 1px solid #ddd; background: #F5F5F5;margin-top: 3px;padding-left: 7px;'>" . $toc->getCondition() . "</div>";
        }

        return new Response(implode("", $tocArr));
    }

    public function commentAction(Request $request)
    {
        $requisitionCommentArray = $request->request->get('requisitionCommentArray');
        $requisitionCommentArray = explode(',',$requisitionCommentArray);

        $comment = $requisitionCommentArray[0];
        $reqId = $requisitionCommentArray[1];

        if($comment) {
            $purchaseOrderComment = new PurchaseOrderComment();

            $purchaseOrderComment->setCreatedBy($this->getUser());
            $purchaseOrderComment->setCreatedDate(new \DateTime());

            $purchaseOrderComment->setPurchaseOrder($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->findOneById($reqId));
            $purchaseOrderComment->setComment($comment);

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderComment')->create($purchaseOrderComment);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }
        return new Response($return);
    }

    public function purchaseOrderTotalQuantityAction(Request $request)
    {
        $item = $request->request->get('item');

        $totalQuantityPri = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->totalQuantity($item);
        $totalQuantityPoi = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->totalQuantity($item);

        if ($totalQuantityPoi[0]['totalQuantity'] == null) {
            $totalQuantityPoi[0]['totalQuantity'] = 0;
        }

        $totalRequiredOfQuantity = ($totalQuantityPri[0]['totalQuantity']) - ($totalQuantityPoi[0]['totalQuantity']);

        $return = json_encode(array("responseCode" => 200, "quantity" => $totalRequiredOfQuantity));

        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param $role
     */
    protected function sendEmail(PurchaseOrder $purchaseOrder, $role)
    {
        $emails = $this->getEmailAddress($role);

        foreach ($emails as $email) {
            $emailArray[] = implode(',', $email);
        }

        $reqNo = $purchaseOrder->getId();
        $amount = $purchaseOrder->getNetTotal();
        $emailFrom = $this->getUser()->getEmail();

        $emailSend = $this->emailBody($emailFrom, $emailArray, $reqNo, $amount);

        $this->get('mailer')->send($emailSend);
    }

    /**
     * @return mixed
     * @param $role
     */
    protected function getEmailAddress($role)
    {
        $emailQuery = $this->getDoctrine()->getRepository('UserBundle:Group')
            ->createQueryBuilder('g')
            ->select('u.email')
            ->where("g.roles LIKE :role")
            ->setParameter('role', $role)
            ->join('g.users', 'u');
        $emails = $emailQuery->getQuery()->getResult();

        return $emails;
    }

    /**
     * @param $emailFrom
     * @param $emailArray
     * @param $reqNo
     * @param $amount
     * @return mixed
     */
    protected function emailBody($emailFrom, $emailArray, $reqNo, $amount)
    {
        $emailSend = \Swift_Message::newInstance()
            ->setSubject('New Purchase Order')
            ->setFrom($emailFrom)
            ->setTo($emailArray)
            ->setBody(
                '<html>' .
                ' <head></head>' .
                ' <body>' .
                ' PO Number ' . $reqNo . ' has been created and waiting for your approve.' .
                ' Total amount ' . $amount .
                ' Waiting for PO Issue' .
                ' </body>' .
                '</html>',
                'text/html' // Mark the content-type as HTML
            );

        return $emailSend;
    }

    public function purchaseOrderAutoCompleteAction(Request $request){

        $poNo = $_REQUEST['q'];

        if ($poNo) {
            $poNoQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
                ->poNoAutoComplete($poNo, $this->getUser());

        }

        return new JsonResponse($poNoQuery);
    }

    public function purchaseOrderRefAutoCompleteAction(Request $request){

        $poRef = $_REQUEST['q'];

        if ($poRef) {
            $poRefQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
                ->poRefAutoComplete($poRef,$this->getUser());

        }

        return new JsonResponse($poRefQuery);
    }

    public function purchaseOrderRefDataAutoCompleteAction($id){
        return new JsonResponse(array(
            'id'=>$id,
            'text'=>$id
        ));
    }

    /**
     * @param $orderItems
     * @param $purchaseOrder
     * @return array
     */
    protected function getOrderFormContent($orderItems, $purchaseOrder)
    {
        $j = 0;
        foreach ($orderItems as $orderItem) {
            $purchaseOrderItem = new PurchaseOrderItem();

            $purchaseRequisitionItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->find($orderItem);

            $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($purchaseRequisitionItem->getItem());

            $purchaseRequisitionNumber = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());

            $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($purchaseRequisitionItem->getPurchaseRequisition());

            $project = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($purchaseRequisition->getProject());

            $quantityRequisition = $purchaseRequisitionItem->getQuantity();

            $quantityPurchaseOrder = $purchaseRequisitionItem->getPurchaseOrderQuantity();

            $quantityRest = ($quantityRequisition - $quantityPurchaseOrder);

            $purchaseOrderItem->setPurchaseRequisitionItem($purchaseRequisitionItem);
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



    public function getAmendmentPoAction(Request $request){

        $poId = $request->attributes->get('poId');
        $amendedPo = $this->getDoctrine()
                          ->getRepository('PmsCoreBundle:PurchaseOrder')
                          ->findBy(array('amendmentByPoNo'=>$poId));
        return new Response($amendedPo?'Amendment '.$amendedPo[0]->getId():'');
    }
} 