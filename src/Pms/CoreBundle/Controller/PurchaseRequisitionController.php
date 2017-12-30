<?php

namespace Pms\CoreBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\Repository;
use Knp\Snappy\Pdf;
use Pms\CoreBundle\Entity\Log\PrLog;
use Pms\CoreBundle\Entity\PurchaseRequisition;
use Pms\CoreBundle\Entity\PurchaseRequisitionComment;
use Pms\CoreBundle\Entity\PurchaseRequisitionItem;
use Pms\CoreBundle\Form\PurchaseRequisitionType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Pms\CoreBundle\Form\SearchForm\PurchaseRequisitionSearchType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;

/**
 * Purchase Requisition controller.
 *
 */
class PurchaseRequisitionController extends Controller
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

        $purchaseRequisitionRepository = $this->getRepositoryFunction();
        $userRole = array($this->getUser()->getRole());

        $form = new PurchaseRequisitionSearchType($this->getUser(), $userRole);

        $data = $request->query->get($form->getName());
        $formSearch = $this->createForm($form, $data);
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if($mobileDetector->isMobile() OR $mobileDetector->isTablet() ){
            if(empty($data)){
                $status = $status == '' ? $status : 'open';
            }
            $purchaseRequisitions = $this->paginate($purchaseRequisitionRepository->getPurchaseRequisitionSearch($this->getUser(), $data, $status, $month));
            return $this->render('PmsCoreBundle:PurchaseRequisition:homeMobile.html.twig', array(
                'purchaseRequisitions' => $purchaseRequisitions,
                'formSearch' => $formSearch->createView(),
                'status' => $status,
            ));
        }

        $purchaseRequisitions = $this->paginate($purchaseRequisitionRepository->getPurchaseRequisitionSearch($this->getUser(), $data, $status, $month));
        return $this->render('PmsCoreBundle:PurchaseRequisition:home.html.twig', array(
            'purchaseRequisitions' => $purchaseRequisitions,
            'formSearch' => $formSearch->createView(),
            'status' => $status,
        ));
    }

    public function purchaseRequisitionAutoCompleteAction(Request $request)
    {
         $prNo = $_REQUEST['q'];

        if ($prNo) {
            $prNoQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
                                             ->prNoAutoComplete($prNo,$this->getUser());
        }

        return new JsonResponse($prNoQuery);
    }
    public function purchaseRequisitionRefAutoCompleteAction(Request $request)
    {
         $prRef = $_REQUEST['q'];

        if ($prRef) {
            $prRefQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
                                             ->prRefAutoComplete($prRef,$this->getUser());
        }

        return new JsonResponse($prRefQuery);
    }
    public function purchaseRequisitionRefDataAutoCompleteAction($id)
    {
        return new JsonResponse(array(
            'id' => $id,
            'text' => $id
        ));
    }

    public function checkPoAction(PurchaseRequisition $purchaseRequisition)
    {
        $poQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->createQueryBuilder('poi')
            ->join('poi.purchaseOrder', 'po')
            ->join('poi.purchaseRequisitionItem', 'pri')
            ->select('po.id')
            ->where('pri.purchaseRequisition = :prId')
            ->setParameter('prId', $purchaseRequisition)
            ->groupBy('po.id');
        $checkPos = $poQuery->getQuery()->getArrayResult();

        $checkPosArr = array();
        foreach ($checkPos as $checkPo) {
            $checkPosArr[] = $checkPo['id'];
        }

        return new Response(implode(", ", $checkPosArr));
    }

    public function detailsAction(PurchaseRequisition $purchaseRequisition)
    {

        $budgetCostByPrProjectAndSubCategoryWise = $this->getDoctrine()
                                                        ->getRepository('PmsBudgetBundle:Budget')
                                                        ->getTotalBudgetByPrProjectAndSubCategoryWise($purchaseRequisition);

        $lastItemHistory = $this->getDoctrine()
                                ->getRepository('PmsCoreBundle:PurchaseRequisition')
                                ->getItemLastQuantity($purchaseRequisition);
      //  var_dump($lastItemHistory);die;
  /*      $projectAndItemWiseItemList = $this->getDoctrine()
                                            ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                                            ->getLastItemRequisitionByProjectAndItemWise($purchaseRequisition);*/




        return $this->render('PmsCoreBundle:PurchaseRequisition:details.html.twig', array(
            'pr' => $purchaseRequisition,
            'budgetAmount'=>$budgetCostByPrProjectAndSubCategoryWise,
            'lastItemHistory' => $lastItemHistory,
        ));
    }

    public function detailsOthersAction(PurchaseRequisition $purchaseRequisition)
    {
        $lastItemHistory = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getItemLastQuantity($purchaseRequisition);

        return $this->render('PmsCoreBundle:PurchaseRequisition:detailsOthers.html.twig', array(
            'pr' => $purchaseRequisition,
            'lastItemHistory' => $lastItemHistory,
        ));
    }

    public function detailsForMobileAction(PurchaseRequisition $purchaseRequisition)
    {

            $lastItemHistory = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getItemLastQuantityMobileVersion($purchaseRequisition);
            return $this->render('PmsCoreBundle:PurchaseRequisition:detailsMobile.html.twig', array(
                'pr' => $purchaseRequisition,
                'lastItemHistory' => $lastItemHistory,
            ));

    }

    public function printAction(PurchaseRequisition $purchaseRequisition)
    {
        $lastItemHistory = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getItemLastQuantity($purchaseRequisition);

        return $this->render('PmsCoreBundle:PurchaseRequisition:print.html.twig', array(
            'pr' => $purchaseRequisition,
            'lastItemHistory' => $lastItemHistory,
        ));
    }

    public function refreshAction(PurchaseRequisition $purchaseRequisition)
    {
        $lastItemHistory = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getItemLastQuantity($purchaseRequisition);

        return $this->render('PmsCoreBundle:PurchaseRequisition:refresh.html.twig', array(
            'pr' => $purchaseRequisition,
            'lastItemHistory' => $lastItemHistory,
        ));
    }

    public function pdfAction(PurchaseRequisition $purchaseRequisition)
    {
        $lastItemHistory = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getItemLastQuantity($purchaseRequisition);

        $html = $this->renderView(
            'PmsCoreBundle:PurchaseRequisition:print.html.twig', array(
                'pr' => $purchaseRequisition,
                'lastItemHistory' => $lastItemHistory,
            )
        );

        $wkhtmltopdfPath = 'xvfb-run --server-args="-screen 0, 1280x1024x24" /usr/bin/wkhtmltopdf --use-xserver';
        $snappy = new Pdf($wkhtmltopdfPath);
        $pdf = $snappy->getOutputFromHtml($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="requisition.pdf"');
        echo $pdf;

        return new Response('');
    }

    public function attachmentViewAction(Request $request, PurchaseRequisition $purchaseRequisition, $index)
    {
        if(null !== $response = $this->checkAttachFileByIndex($purchaseRequisition, $index)) {
            return $response;
        }

        if($index == 1){
            $path = $purchaseRequisition->getPath();
        }elseif($index == 2){
            $path = $purchaseRequisition->getPathTwo();
        }elseif($index == 3){
            $path = $purchaseRequisition->getPathThree();
        }

        return $this->render('PmsCoreBundle:PurchaseRequisition:viewer.html.twig', array(
            'pr' => $purchaseRequisition,
            'path' => $path,
        ));
    }

    public function newAction(Request $request)
    {
        $purchaseRequisition = new PurchaseRequisition();
        $userRole = array($this->getUser()->getRole());

        $form = $this->createForm(new PurchaseRequisitionType($this->getUser(), $userRole), $purchaseRequisition);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $itemInfo = $request->request->get('purchaserequisition')['purchaseRequisitionItems'];

                $itemArr = array();
                foreach($itemInfo as $item){
                    $itemArr[] = $item['item'];
                }

                if($this->hasMultipleTime($itemArr) == true){
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'item should not be duplicate'
                    );
                    goto end;
                }

                $purchaseRequisition->setCreatedBy($this->getUser());
                $purchaseRequisition->setCreatedDate(new \DateTime());
                $purchaseRequisition->setDateOfRequisition(new \DateTime());
                $purchaseRequisition->setStatus(1);
                $purchaseRequisition->setApproveStatus(0);
                $purchaseRequisition->setTotalRequisitionItemQuantity(0);
                $purchaseRequisition->setTotalOrderItemQuantity(0);
                $purchaseRequisition->setTotalRequisitionItem(0);
                $purchaseRequisition->setTotalRequisitionItemClaimed(0);
                $purchaseRequisition->setCategory($this->getDoctrine()->getRepository('PmsSettingBundle:Category')->findOneById($form->getData()->getCategory()));

                $i = 1;
                /** @var PurchaseRequisitionItem $item */
                foreach ($purchaseRequisition->getPurchaseRequisitionItems() as $item) {

                    $item->setPurchaseRequisition($purchaseRequisition);
                    $item->setPurchaseOrderQuantity(0);
                    $item->setStatus(1);

                    $purchaseRequisition->setTotalRequisitionItemQuantity(
                        $item->getQuantity() + $purchaseRequisition->getTotalRequisitionItemQuantity()
                    );

                    $purchaseRequisition->setTotalRequisitionItem($i);
                    $i = $i + 1;
                }

                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->create($purchaseRequisition);

//                $this->sendEmail($purchaseRequisition, 'role');

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Purchase Requisition Successfully Add'
                );

                return $this->redirect($this->generateUrl('purchase_requisition'));
            }
        }

        end:
        return $this->render('PmsCoreBundle:PurchaseRequisition:form.html.twig', array(
            'form' => $form->createView(),
            'purchaseRequisition' => ''
        ));
    }

    public function editAction(Request $request, PurchaseRequisition $purchaseRequisition)
    {
//        ini_set('memory_limit', '-1'); // for execute time out or tired

        $userRole = array($this->getUser()->getRole());

        $form = $this->createForm(new PurchaseRequisitionType($this->getUser(), $userRole), $purchaseRequisition);


         $this->deletePurchaseRequisitionItem($request, $purchaseRequisition);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                if (!empty($_POST['purchaserequisition']['purchaseRequisitionItems'])) {

                    $purchaseRequisition->setUpdatedBy($this->getUser());
                    $purchaseRequisition->setUpdatedDate(new \DateTime());

                    $i = 0;
                    $totalPurchaseRequisitionItemQuantity = 0;
                    $totalPurchaseRequisitionItem = 0;
                    /** @var PurchaseRequisitionItem $item */



                    foreach ($form->getData()->getPurchaseRequisitionItems() as $item) {

                        if ($item->getId() == null && $item->getQuantity() > 0 && $item->getItem() != ""  ) {

                            $totalPurchaseRequisitionItem += COUNT($item);
                            $totalPurchaseRequisitionItemQuantity += $item->getQuantity();

                            $item->setPurchaseRequisition($purchaseRequisition);

                            $item->setPurchaseOrderQuantity(0);
                            $item->setStatus(1);
                            $purchaseRequisition->addPurchaseRequisitionItem($item);
                            $i++;

                        } else {

                            $purchaseOrderItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findOneBy(array('purchaseRequisitionItem'=>$item));
                            if($purchaseOrderItem){
                                $purchaseOrderItem->setItem($item->getItem());
                            }

                            $totalPurchaseRequisitionItem += COUNT($item);
                            $totalPurchaseRequisitionItemQuantity += $item->getQuantity();
                        }


                    }
                    $purchaseRequisition->setTotalRequisitionItemQuantity($totalPurchaseRequisitionItemQuantity);
                    $purchaseRequisition->setTotalRequisitionItem($totalPurchaseRequisitionItem);
                }


                $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);

//                $this->sendEmail($purchaseRequisition, 'role');

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Purchase Requisition Successfully Update'
                );

                return $this->redirect($this->generateUrl('purchase_requisition'));
            }
        }

        return $this->render('PmsCoreBundle:PurchaseRequisition:form.html.twig', array(
            'purchaseRequisition' => $purchaseRequisition,
            'form' => $form->createView(),
        ));
    }

    public function holdAction(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->setStatus(5);
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);

        $this->keepLog($purchaseRequisition, 5);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Purchase Requisition Successfully Hold'
        );

        return $this->redirect($this->generateUrl('purchase_requisition'));
    }

    public function openAction(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->setStatus(1);
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);

        $this->keepLog($purchaseRequisition, 1);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Purchase Requisition Successfully Open'
        );

        return $this->redirect($this->generateUrl('purchase_requisition'));
    }

    public function cancelAction(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->setStatus(6);
        $purchaseRequisition->setClosedBy($this->getUser());
        $purchaseRequisition->setClosedDate(new \DateTime());
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);

        $this->keepLog($purchaseRequisition, 6);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Purchase Requisition Successfully Cancel'
        );

        return $this->redirect($this->generateUrl('purchase_requisition'));
    }

    public function approveByProjectHeadAction(PurchaseRequisition $purchaseRequisition)
    {
        if($purchaseRequisition->getApproveStatus() == 0){

            $purchaseRequisition->setApproveStatus(1);
            $purchaseRequisition->setApprovedByProjectHead($this->getUser());
            $purchaseRequisition->setApprovedDateProjectHead(new \DateTime());
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Requisition Successfully Approved'
            );
        }

        return $this->redirect($this->generateUrl('purchase_requisition'));
    }

    public function approveByCategoryHeadOneAction(PurchaseRequisition $purchaseRequisition)
    {
        if($purchaseRequisition->getApproveStatus() == 1){

            $purchaseRequisition->setApproveStatus(2);
            $purchaseRequisition->setApprovedByCategoryHeadOne($this->getUser());
            $purchaseRequisition->setApprovedDateCategoryHeadOne(new \DateTime());
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Requisition Successfully Approved'
            );
        }

        return $this->redirect($this->generateUrl('purchase_requisition'));
    }

    public function approveByCategoryHeadTwoAction(PurchaseRequisition $purchaseRequisition)
    {
        if($purchaseRequisition->getApproveStatus() == 2){
            $purchaseRequisition->setApproveStatus(3);
            $purchaseRequisition->setApprovedByCategoryHeadTwo($this->getUser());
            $purchaseRequisition->setApprovedDateCategoryHeadTwo(new \DateTime());
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisition);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Purchase Requisition Successfully Approved'
            );
        }

        return $this->redirect($this->generateUrl('purchase_requisition'));
    }

    public function categoryWiseItemAction(Request $request)
    {
        $categoryId = $request->request->get('category');

        $items = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getCategoryBasedItems($categoryId);

        $categoryWiseItemArr = array();
        foreach ($items as $item) {
            $categoryWiseItemArr[] = $item;
        }

        $response = new Response(json_encode(array(
            'categoryWiseItem' => $categoryWiseItemArr
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function itemWiseCategoryAction(Request $request)
    {
        $itemId = $request->request->get('item');

        $categories = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getCategories($itemId);

        $categoryWiseItemArr = array();
        foreach ($categories as $category) {
            $categoryWiseItemArr[] = $category;
        }

        $response = new Response(json_encode(array(
            'categoryWiseItem' => $categoryWiseItemArr
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function categoryWiseSubCategoryAction(Request $request)
    {
        $categoryId = $request->request->get('category');

        $subCategories = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->getSubCategories($categoryId);

        $subCategoryArr = array();
        foreach ($subCategories as $subCategory) {
            $subCategoryArr[] = $subCategory;
        }

        $response = new Response(json_encode(array(
                'subCategory' => $subCategoryArr
            )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function subCategoryWiseCostHeaderAction(Request $request)
    {
        $subCategoryId = $request->request->get('subCategory');

        $costHeaders = $this->getDoctrine()->getRepository('PmsSettingBundle:CostHeader')->getCostHeaders($subCategoryId);

        $costHeaderArr = array();
        foreach ($costHeaders as $costHeader) {
            $costHeaderArr[] = $costHeader;
        }

        $response = new Response(json_encode( array( 'costHeader' => $costHeaderArr )));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function itemTypeAction(Request $request)
    {
        $itemId = $request->request->get('item');

        $itemTypes = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemTypes($itemId);

        list($itemType, $itemUnit, $price) = $this->getItemTypeUnitPrice($itemTypes);

        $response = new Response(json_encode(array(
            'itemType' => $itemType,
            'itemUnit' => $itemUnit,
            'price' => $price
        )));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function claimAction(Request $request)
    {
        $id = $request->request->get('reqItemId');

        $purchaseRequisitionItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->find($id);
        $claimBy = $purchaseRequisitionItem->getClaimedBy();
        $purchaseRequisitionItem->setClaimedBy($this->getUser());
        $purchaseRequisitionItem->setClaimedDate(new \DateTime());

        if (is_null($claimBy)){
            $purchaseRequisitionItem->getPurchaseRequisition()->setTotalRequisitionItemClaimed(1 + $purchaseRequisitionItem->getPurchaseRequisition()->getTotalRequisitionItemClaimed());
        } else {
            $purchaseRequisitionItem->getPurchaseRequisition()->setTotalRequisitionItemClaimed($purchaseRequisitionItem->getPurchaseRequisition()->getTotalRequisitionItemClaimed());
        }
        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->update($purchaseRequisitionItem);

        return new Response(json_encode(array("responseCode" => 202)));
    }


    public function approveByProjectHeadAjaxAction(Request $request)
    {
        $id = $request->request->get('reqId');

        $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($id);
        if($purchaseRequisition->getApproveStatus() == 0){

            $purchaseRequisition->setApproveStatus(1);
            $purchaseRequisition->setApprovedByProjectHead($this->getUser());
            $purchaseRequisition->setApprovedDateProjectHead(new \DateTime());
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
                                ->update($purchaseRequisition);
        }
        return new Response(json_encode(array("responseCode" => 202)));
    }

    public function approveByCategoryHeadOneAjaxAction(Request $request)
    {
        $id = $request->request->get('reqId');

        $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($id);
        if($purchaseRequisition->getApproveStatus() == 1) {

            $purchaseRequisition->setApproveStatus(2);
            $purchaseRequisition->setApprovedByCategoryHeadOne($this->getUser());
            $purchaseRequisition->setApprovedDateCategoryHeadOne(new \DateTime());
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
                                ->update($purchaseRequisition);
        }

        return new Response(json_encode(array("responseCode" => 202)));
    }

    public function approveByCategoryHeadTwoAjaxAction(Request $request)
    {
        $id = $request->request->get('reqId');

        $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($id);
        if($purchaseRequisition->getApproveStatus() == 2) {
            $purchaseRequisition->setApproveStatus(3);
            $purchaseRequisition->setApprovedByCategoryHeadTwo($this->getUser());
            $purchaseRequisition->setApprovedDateCategoryHeadTwo(new \DateTime());
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
                                ->update($purchaseRequisition);
        }
        return new Response(json_encode(array("responseCode" => 202)));
    }

    public function commentAction(Request $request)
    {
        $requisitionCommentArray = $request->request->get('requisitionCommentArray');
        $requisitionCommentArray = explode(',', $requisitionCommentArray);

        $comment = $requisitionCommentArray[0];
        $reqId = $requisitionCommentArray[1];

        if ($comment) {
            $commentReq = new PurchaseRequisitionComment();

            $commentReq->setComment($comment);
            $commentReq->setCreatedBy($this->getUser());
            $commentReq->setCreatedDate(new \DateTime());
            $commentReq->setPurchaseRequisition($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->find($reqId));

            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->create($commentReq);

            $return = array("responseCode" => 202);
        } else {

            $return = array("responseCode" => 204);
        }

        return new Response(json_encode($return));
    }

    public function deletePrItemAction(PurchaseRequisitionItem $purchaseRequisitionItem)
    {

        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->delete($purchaseRequisitionItem);

        return new Response('ok');


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
     * @param PurchaseRequisition $purchaseRequisition
     * @param $role
     */
    protected function sendEmail(PurchaseRequisition $purchaseRequisition, $role)
    {
        $emails = $this->getEmailAddress($role);

        $emailArray = '';
        foreach ($emails as $email) {
            $emailArray[] = implode(',', $email);
        }

        $reqNo = $purchaseRequisition->getId();
        $project = $purchaseRequisition->getProject()->getProjectName();
        $emailFrom = $this->getUser()->getEmail();

        $emailSend = $this->emailBody($emailFrom, $emailArray, $reqNo, $project);

        $this->get('mailer')->send($emailSend);
    }

    /**
     * @param $emailFrom
     * @param $emailArray
     * @param $reqNo
     * @param $project
     * @return \Swift_Mime_MimePart
     */
    protected function emailBody($emailFrom, $emailArray, $reqNo, $project)
    {
        $emailSend = \Swift_Message::newInstance()
            ->setSubject('New Purchase Requisition')
            ->setFrom($emailFrom)
            ->setTo($emailArray)
            ->setBody(
                '<html>' .
                ' <head></head>' .
                ' <body>' .
                ' PR Number ' . $reqNo . ' has been validated and waiting for your verify.' .
                ' Project Name :' . $project .
                ' </body>' .
                '</html>',
                'text/html' // Mark the content-type as HTML
            );

        return $emailSend;
    }

    private function checkAttachFileByIndex(PurchaseRequisition $purchaseRequisition, $index)
    {
        if (null == $fileName = $purchaseRequisition->getAbsolutePathByIndex($index)) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    /**
     * @return \Pms\CoreBundle\Entity\Repository\PurchaseRequisitionRepository
     */
    protected function getRepositoryFunction()
    {
        $purchaseRequisitionRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition');

        return $purchaseRequisitionRepository;
    }

    /**
     * @param $itemTypes
     * @return array
     */
    protected function getItemTypeUnitPrice($itemTypes)
    {
        if ($itemTypes) {
            $itemType = $itemTypes[0]['itemType'];
            $itemUnit = $itemTypes[0]['itemUnit'];
            $price = $itemTypes[0]['price'];
            return array($itemType, $itemUnit, $price);
        } else {
            $itemType = '';
            $itemUnit = '';
            $price = '';
            return array($itemType, $itemUnit, $price);
        }
    }

    /**
     * @param $role
     * @return mixed
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
     * @param PurchaseRequisition $purchaseRequisition
     * @param $status
     */
    protected function keepLog(PurchaseRequisition $purchaseRequisition, $status)
    {
        $prLog = new PrLog();

        $prLog->setPurchaseRequisition($purchaseRequisition);
        $prLog->setCreatedBy($this->getUser());
        $prLog->setStatus($status);
        $this->getDoctrine()->getRepository('PmsCoreBundle:Log\PrLog')->create($prLog);
    }

    /**
     * @param $itemArr
     * @return array
     */
    protected function hasMultipleTime($itemArr)
    {
        return max(array_count_values($itemArr)) > 1;
    }

    /**
     * @param Request $request
     * @param PurchaseRequisition $purchaseRequisition
     * @return mixed|null
     */
    private function deletePurchaseRequisitionItem(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($request->getMethod() == 'POST') {
            $newItemLists = array();
            if(!empty($_POST['purchaserequisition']['purchaseRequisitionItems'])){
                foreach ($_POST['purchaserequisition']['purchaseRequisitionItems'] as $newItem) {
                    if(isset($newItem['id'])){
                        $newItemLists[$newItem['id']] = $newItem['id'];
                    }
                }
            }
            $removableLists = array();
            foreach ($purchaseRequisition->getPurchaseRequisitionItems() as $item) {
                if (!isset($newItemLists[$item->getId()])) {
                    $removableLists[] = $item;
                }
            }
            foreach ($removableLists as $removableList) {
                $purchaseOrderItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->find($removableList);
                if(!empty($purchaseOrderItem)){
                    $this->getDoctrine()
                        ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                        ->delete($removableList);
                }

            }
            return $item;
        }



    }


}