<?php

namespace Pms\ReportBundle\Controller;

use DateTime;
use Pms\BudgetBundle\Form\SearchForm\DateSearchType;
use Doctrine\ORM\Repository;
use Pms\CoreBundle\Form\SearchForm\PurchaseRequisitionSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver;

class PurchaseOrderActivitiesReportController extends Controller
{
    public function requisitionActivitiesSearchForm($request)
    {
        $form = new DateSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function purchaseActivitiesAction(Request $request)
    {

        $requestParam = $request->query->get('search');

        if($requestParam == null ){
            $requestParam['month'] ='';
        }

        list($form, $data) = $this->requisitionActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($totalPurchase,$totalPurchaseOpen,$totalPurchaseApproved, $totalPurchaseInProgress,
            $totalPurchaseResolved,$totalPurchaseHold ,$totalPurchaseCanceled,$totalPurchaseAmendment) =
                                                    $this->getDoctrine()
                                                         ->getRepository('PmsCoreBundle:PurchaseOrder')
                                                         ->purchaseActivities($data);

        list($totalPurchaseItem,$totalPurchaseOpenItem,$totalPurchaseApprovedItem, $totalPurchaseInProgressItem,
            $totalPurchaseResolvedItem,$totalPurchaseHoldItem ,$totalPurchaseCanceledItem,$totalPurchaseAmendmentItem,
            $totalPurchaseItemIndividualCancel) =
                                                    $this->getDoctrine()
                                                         ->getRepository('PmsCoreBundle:PurchaseOrder')
                                                         ->purchaseOderItemActivities($data);

        /*$activitiesResolvedDayCount = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
                                           ->requisitionActivitiesAverageDayCount($data);*/

        /*if (!empty($data)){
            list($resolvedActivityForBarChart, $maxKeyValue, $averageDate) = $this->barChartCalculationAndAverage($activitiesResolvedDayCount);
        }else{
            $resolvedActivityForBarChart = array();
            $maxKeyValue = 0;
            $averageDate = 0;
        }*/

        return $this->render('PmsReportBundle:Report:purchaseOrderActivitiesReport.html.twig', array(

            'totalPurchase'                  => $totalPurchase,
            'totalPurchaseOpen'              => $totalPurchaseOpen,
            'totalPurchaseApproved'          => $totalPurchaseApproved,
            'totalPurchaseResolved'          => $totalPurchaseResolved,
            'totalPurchaseCanceled'          => $totalPurchaseCanceled,
            'totalPurchaseInProgress'        => $totalPurchaseInProgress,
            'totalPurchaseHold'              => $totalPurchaseHold,
            'totalPurchaseAmendment'         => $totalPurchaseAmendment,
            'totalRequisitionItemTotal'         => $totalPurchaseItem,
            'totalRequisitionItemOpen'          => $totalPurchaseOpenItem,
            'totalRequisitionItemApproved'      => $totalPurchaseApprovedItem,
            'totalRequisitionItemInProgress'    => $totalPurchaseInProgressItem,
            'totalRequisitionItemResolved'      => $totalPurchaseResolvedItem,
            'totalRequisitionItemHold'          => $totalPurchaseHoldItem,
            'totalRequisitionItemCanceled'      => $totalPurchaseCanceledItem,
            'totalRequisitionItemAmendment'      => $totalPurchaseAmendmentItem,
            'totalRequisitionItemCanceledIndividual'      => $totalPurchaseItemIndividualCancel,
            'activitiesResolvedDayCount'        => '',
            'resolvedActivityForBarChart'       => '',
            'maxKeyValue'                       => '',
            'averageDate'                       => '',
            'requestParam'                      => $requestParam,
            'form'                              => $form->createView(),
        ));
    }

    /**
     * @param $activitiesResolvedDayCount
     * @return array
     */
    protected function barChartCalculationAndAverage($activitiesResolvedDayCount)
    {
        $activity = array();
        foreach ($activitiesResolvedDayCount as $key => $activitiesResolved) {
            $activity[$key] = intVal($activitiesResolved['prAverageDates']);
        }

        $resolvedActivityForBarChart = array_count_values($activity);

        $keyArray = array();
        foreach ($resolvedActivityForBarChart as $key => $resolvedActivityForBar) {
            $keyArray[] = $key;
        }
        if($keyArray==null){ $keyArray[0]=0; }
        $maxKeyValue = max($keyArray);

        $arrLength = sizeof($keyArray);
        $arrSum = array_sum($keyArray);
        $averageDate = $arrSum / $arrLength;
        return array($resolvedActivityForBarChart, $maxKeyValue, $averageDate);
    }

    public function itemActivitiesDetailAction(Request $request, $status = 'all')
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
            if(!isset($month['month'])) {
                $month['month'] = '';
            }
        }else{
            $month = 1;
        }

        $purchaseRequisitionItems = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getPurchaseRequisitionData($month,$status));
        

        return $this->render('PmsReportBundle:Report:itemCancelActivitiesDetails.html.twig', array(
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
            'status' => $status,
        ));
    }
    public function purchaseOrderItemActivitiesDetailAction(Request $request, $status = 'all')
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
            if(!isset($month['month'])) {
                $month['month'] = '';
            }
        }else{
            $month = 1;
        }

        $purchaseOrderRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem');
        $purchaseOrderItems = $this->paginate($purchaseOrderRepository->getPurchaseOrderItemList($status,$month));

        return $this->render('PmsReportBundle:Report:purchaseOrderItemActivitiesDetails.html.twig', array(
            'purchaseOrders' => $purchaseOrderItems,
            'status' => $status,
        ));
    }
    public function itemCancelActivitiesDetailAction(Request $request, $status = 'all')
    {
        if(!empty($request->query->all())){
            $month = $request->query->all();
        }else{
            $month = 1;
        }
        $purchaseRequisitionItems = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getPurchaseRequisitionCancelData($month,$status));

        return $this->render('PmsReportBundle:Report:itemCancelActivitiesDetails.html.twig', array(
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
            'status' => $status,
        ));
    }
    public function totalRequisitionItemDetailAction(Request $request)
    {
        if(!empty($request->query->all())){
            $month = $request->query->all();
        }else{
            $month = 1;
        }
        $purchaseRequisitionItems = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getPurchaseRequisitionItemTotalData($month));

        return $this->render('PmsReportBundle:Report:totalPurchaseRequisitionitemDetails.html.twig', array(
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
            'status'=>'Total',
        ));
    }
    public function itemActivitiesDetailIndividualItemAction(Request $request)
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
        }else{
            $month = 1;
        }

        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItemCloseInfo')->getPurchaseRequisitionIndividualData($month);

        return $this->render('PmsReportBundle:Report:itemIndividualActivitiesDetails.html.twig', array(
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
            'status' => 'individual-closed',
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
        $value     = $paginator->paginate(
            $query,
            $page = $this->get('request')->query->get('page', 1) /*page number*/,
            25/*limit per page*/
        );

        return $value;
    }

}