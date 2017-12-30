<?php

namespace Pms\ReportBundle\Controller;

use DateTime;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\BudgetBundle\Form\SearchForm\DateSearchType;
use Doctrine\ORM\Repository;
use Pms\CoreBundle\Form\SearchForm\PurchaseRequisitionSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver;

class RequisitionActivitiesReportController extends Controller
{
    public function requisitionActivitiesSearchForm($request)
    {
        $form = new DateSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function requisitionActivitiesAction(Request $request)
    {
        $requestParam = $request->query->get('search');

        if($requestParam == null ){
            $requestParam['month'] ='';
        }

        list($form, $data) = $this->requisitionActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($totalRequisition,  $totalRequisitionResolved, $totalRequisitionCanceled, $totalRequisitionOpen,
            $totalRequisitionApproved, $totalRequisitionInProgress, $totalRequisitionHold,$totalRequisitionClosed,
            $totalRequisitionItemResolved, $totalRequisitionItemCanceled, $totalRequisitionItemCanceledIndividual,
            $totalRequisitionItemInProgress, $totalRequisitionItemOpen,$totalRequisitionItemHold,$totalRequisitionItemApproved,$totalRequisitionItemTotal) =
            $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->requisitionActivities($data);
        $activitiesResolvedDayCount = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->requisitionActivitiesAverageDayCount($data);

        if (!empty($data)){
            list($resolvedActivityForBarChart, $maxKeyValue, $averageDate) = $this->barChartCalculationAndAverage($activitiesResolvedDayCount);
        }else{
            $resolvedActivityForBarChart = array();
            $maxKeyValue = 0;
            $averageDate = 0;
        }

        return $this->render('PmsReportBundle:Report:requisitionActivitiesReport.html.twig', array(
            'totalRequisitionItemOpen'          => $totalRequisitionItemOpen,
            'totalRequisition'                  => $totalRequisition,
            'totalRequisitionOpen'              => $totalRequisitionOpen,
            'totalRequisitionApproved'          => $totalRequisitionApproved,
            'totalRequisitionResolved'          => $totalRequisitionResolved,
            'totalRequisitionCanceled'          => $totalRequisitionCanceled,
            'totalRequisitionInProgress'        => $totalRequisitionInProgress,
            'totalRequisitionHold'              => $totalRequisitionHold,
            'totalRequisitionClosed'            => $totalRequisitionClosed,
            'totalRequisitionItemTotal'         => $totalRequisitionItemTotal,
            'totalRequisitionItemHold'          => $totalRequisitionItemHold,
            'totalRequisitionItemResolved'      => $totalRequisitionItemResolved,
            'totalRequisitionItemCanceled'      => $totalRequisitionItemCanceled,
            'totalRequisitionItemCanceledIndividual'      => $totalRequisitionItemCanceledIndividual,
            'totalRequisitionItemInProgress'    => $totalRequisitionItemInProgress,
            'totalRequisitionItemApproved'      => $totalRequisitionItemApproved,
            'activitiesResolvedDayCount'        => $activitiesResolvedDayCount,
            'resolvedActivityForBarChart'       => $resolvedActivityForBarChart,
            'maxKeyValue'                       => $maxKeyValue,
            'averageDate'                       => $averageDate,
            'requestParam'                      => $requestParam,
            'form'                              => $form->createView(),
        ));
    }

    public function prActivitiesReportExcelAction(Request $request)
    {

        list($form, $data) = $this->requisitionActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->requisitionActivities($data);
        $activitiesResolvedDayCounts = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->requisitionActivitiesAverageDayCount($data);
       // var_dump($activitiesResolvedDayCount);die;

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'PR No',
            'B9'=>'Average Claim Days',
            'C9'=>'Average PO Issued Days',
            'D9'=>'Project',
            'E9'=>'Sub  Category',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'prActivities'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'PR Activities Report');

        if(!empty($activitiesResolvedDayCounts)){

            $index = 11;
            $count = 0;

            foreach( $activitiesResolvedDayCounts as $activitiesResolvedDayCount) {

                    $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $activitiesResolvedDayCount['prId']);
                    $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $activitiesResolvedDayCount['claimAverageDates']? $activitiesResolvedDayCount['claimAverageDates']:'0');
                    $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $activitiesResolvedDayCount['prAverageDates']? $activitiesResolvedDayCount['prAverageDates']:'0');
                    $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $activitiesResolvedDayCount['project']? $activitiesResolvedDayCount['project']:'0');
                    $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $activitiesResolvedDayCount['sub_category']? $activitiesResolvedDayCount['sub_category']:'');

                $index++;
                $count++;
            }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
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

    public function prItemApprovedReportExcelAction(Request $request,$status)
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
            if(!isset($month['month'])) {
                $month['month'] = '';
            }
        }else{
            $month = 1;
        }


        $purchaseRequisitionItems = $this->getDoctrine()
                                         ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                                         ->getPurchaseRequisitionOpenData($month,$status);


        $header_arrays = array(
            'B9' => 'S/L',
            'C9' => 'Item',
            'D9' => 'PR No',
            'E9' => 'Project',
            'F9' => 'Approve Date',
            'G9' => 'Quantity',
            'H9' => 'Cancel Quantity',
            'I9' => 'Unit',
            'J9' => 'Local/Head',
            'K9' => 'Status',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PrItemApprovedRequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PR item Approved Excel Report');
        if(!empty($purchaseRequisitionItems)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionItems as $list) {

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list->getItem()?$list->getItem()->getItemName():'');
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list->getPurchaseRequisition()?$list->getPurchaseRequisition()->getId():'');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list->getPurchaseRequisition()->getProject() ? $list->getPurchaseRequisition()->getProject()->getProjectName():'');
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo()->format('d-M-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list->getQuantity() ? $list->getQuantity():'');
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $list->getPurchaseRequisitionItemCloseInfo()?
                                                                         $list->getPurchaseRequisitionItemCloseInfo()->getQuantity():0);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $list->getItem()?$list->getItem()->getItemUnit():'');
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $list->getIsHeadOrLocal().' office');
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, 'approve');

                $index++;
                $count++;
            }
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }
        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
    }
    public function prItemInProgressReportExcelAction(Request $request,$status)
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
            if(!isset($month['month'])) {
                $month['month'] = '';
            }
        }else{
            $month = 1;
        }

        $purchaseRequisitionItems = $this->getDoctrine()
                                         ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                                         ->getPurchaseRequisitionData($month,$status);

        $header_arrays = array(
            'B9' => 'S/L',
            'C9' => 'Item',
            'D9' => 'PR No',
            'E9' => 'Project',
            'F9' => 'Approve Date',
            'G9' => 'Quantity',
            'H9' => 'Cancel Quantity',
            'I9' => 'Unit',
            'J9' => 'Local/Head',
            'K9' => 'Status',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PrItemApprovedRequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PR item In-progress Excel Report');
        if(!empty($purchaseRequisitionItems)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionItems as $list) {

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list->getItem()->getItemName());
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list->getPurchaseRequisition()->getId());
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list->getPurchaseRequisition()->getProject()->getProjectName());
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo()->format('d-M-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list->getQuantity());
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $list->getPurchaseRequisitionItemCloseInfo()?
                    $list->getPurchaseRequisitionItemCloseInfo()->getQuantity():0);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $list->getItem()->getItemUnit());
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $list->getIsHeadOrLocal().' office');
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, 'in-progress');

                $index++;
                $count++;
            }
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }
        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
    }
    public function prItemCancelReportExcelAction(Request $request,$status)
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
        }else{
            $month = 1;
        }
        $purchaseRequisitionItems = $this->getDoctrine()
                                         ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                                         ->getPurchaseRequisitionCancelData($month,$status);


        $header_arrays = array(
            'B9' => 'S/L',
            'C9' => 'Item',
            'D9' => 'PR No',
            'E9' => 'Project',
            'F9' => 'Approve Date',
            'G9' => 'Quantity',
            'H9' => 'Cancel Quantity',
            'I9' => 'Unit',
            'J9' => 'Local/Head',
            'K9' => 'Status',
            'L9' => 'Close Remark',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PrItemApprovedRequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PR item In-progress Excel Report');
        if(!empty($purchaseRequisitionItems)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionItems as $list) {

                if($list->getPurchaseRequisition()->getClosedBy()){
                    $username =  'Cancel By '.$list->getPurchaseRequisition()->getClosedBy()->getUserName();
                } else {

                    if($list->getPurchaseRequisitionItemCloseInfo() != null){
                        $closedBy = $list->getPurchaseRequisitionItemCloseInfo()->getClosedBy()->getUsername();
                    }  else {
                        $closedBy = "";
                    }
                    $username =  'Closed By '. $closedBy;
                }

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list->getItem()->getItemName());
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list->getPurchaseRequisition()->getId());
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list->getPurchaseRequisition()->getProject()->getProjectName());
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo()?
                                                                         $list->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo()->format('d-M-Y'):'');
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list->getQuantity());
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $list->getPurchaseRequisitionItemCloseInfo()?
                                                                         $list->getPurchaseRequisitionItemCloseInfo()->getQuantity():0);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $list->getItem()->getItemUnit());
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $list->getIsHeadOrLocal().' office');
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $username);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $list->getCloseRemark()?$list->getCloseRemark():'');


                $index++;
                $count++;
            }
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }
        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
    }

    public function prItemCloseReportExcelAction(Request $request)
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
        }else{
            $month = 1;
        }


        $purchaseRequisitionItems = $this->getDoctrine()
                                         ->getRepository('PmsCoreBundle:PurchaseRequisitionItemCloseInfo')
                                         ->getPurchaseRequisitionIndividualData($month);

        $header_arrays = array(
            'B9' => 'S/L',
            'C9' => 'Item',
            'D9' => 'PR No',
            'E9' => 'Project',
            'F9' => 'Item Type',
            'G9' => 'PR Quantity',
            'H9' => 'Cancel Quantity',
            'I9' => 'Unit',
            'J9' => 'Claimed',
            'K9' => 'Local/Head',
            'L9' => 'Status',
            'M9' => 'Required By',
            'N9' => 'Closed By',
            'O9' => 'Close Remark',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PrItemApprovedRequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PR item In-progress Excel Report');
        if(!empty($purchaseRequisitionItems)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionItems as $list) {

            /*    if($list->getPurchaseRequisition()->getClosedBy()){
                    $username =  'Cancel By '.$list->getPurchaseRequisition()->getClosedBy()->getUserName();
                } else{
                    $username =  'Closed By '. $list->getPurchaseRequisitionItemCloseInfo()->getClosedBy()->getUsername();
                }*/

                $itemName = $list->getPurchaseRequisitionItem()->getItem() ?$list->getPurchaseRequisitionItem()->getItem()->getItemName():0;
                $purchaseRequitionId = $list->getPurchaseRequisitionItem()->getPurchaseRequisition()->getId();
                $project = $list->getPurchaseRequisitionItem()->getPurchaseRequisition()?$list->getPurchaseRequisitionItem()->getPurchaseRequisition()->getProject()->getProjectName():0;
                $itemType = $list->getPurchaseRequisitionItem()->getItem()?$list->getPurchaseRequisitionItem()->getItem()->getItemType()->getItemType():0;
                $prQuantity = $list->getPurchaseRequisitionItem()->getQuantity() + $list->getPurchaseRequisitionItem()->getPurchaseRequisitionItemCloseInfo()->getQuantity();
                $prCancelQty =  $list->getPurchaseRequisitionItem()->getPurchaseRequisitionItemCloseInfo() ? $list->getPurchaseRequisitionItem()->getPurchaseRequisitionItemCloseInfo()->getQuantity(): 0;
                $unit =  $list->getPurchaseRequisitionItem()->getItem() ? $list->getPurchaseRequisitionItem()->getItem()->getItemUnit():0;
                $calimedBy = $list->getPurchaseRequisitionItem()? $list->getPurchaseRequisitionItem()->getClaimedBy()->getUsername():'';
                $headLocal = $list->getPurchaseRequisitionItem()->getIsHeadOrLocal()?$list->getPurchaseRequisitionItem()->getIsHeadOrLocal():'';


                if($list->getPurchaseRequisitionItem()->getClaimedBy() !=null){
                    $status = "Claimed";
                } else{
                    $status = "Open";
                }
                $dateOfRequired = $list->getPurchaseRequisitionItem()? $list->getPurchaseRequisitionItem()->getDateOfRequired()->format('Y-m-d'):'';
                $closedBy = $list->getPurchaseRequisitionItem() ? $list->getPurchaseRequisitionItem()
                                                                       ->getPurchaseRequisitionItemCloseInfo()
                                                                        ->getClosedBy()->getUsername():"";

                $remarks = $list->getPurchaseRequisitionItem()->getPurchaseRequisition() ? $list->getPurchaseRequisitionItem()->getCloseRemark():" ";

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $purchaseRequitionId);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $itemType);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $prQuantity);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $prCancelQty);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $unit);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $calimedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $headLocal);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $status);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $dateOfRequired);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $closedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $remarks);


                $index++;
                $count++;
            }
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }
        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
    }
    private function excelSheetSet($header_arrays)
    {
        $redArr = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'f5f5f5')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000000'),
                'size'  => 11,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $objPHPExcel = new PHPExcel();

        //header set
        foreach($header_arrays as $key => $header_array ){

            $objPHPExcel->getActiveSheet()->setCellValue($key, $header_array);
            $objPHPExcel->getActiveSheet()->getStyle($key)->applyFromArray($redArr);
            $objPHPExcel->getActiveSheet()->getColumnDimension($key[0])->setWidth(22);
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(15);
        }

        return $objPHPExcel;
    }
    public function itemOpenActivitiesDetailAction(Request $request, $status = 'all')
    {

        if(!empty($request->query->all())){
            $month = $request->query->all();
            if(!isset($month['month'])) {
                $month['month'] = '';
            }
        }else{
            $month = 1;
        }


        $purchaseRequisitionItems = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getPurchaseRequisitionOpenData($month,$status));


        return $this->render('PmsReportBundle:Report:itemCancelActivitiesDetails.html.twig', array(
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
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