<?php

namespace Pms\ReportBundle\Controller;

use Pms\ReportBundle\Form\PurchaseRequisitionReportSearchType;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;

use Doctrine\ORM\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class PurchaseRequisitionReportController extends Controller
{
    public function requisitionReportSearchForm($request)
    {
        $form = new PurchaseRequisitionReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function requisitionAction(Request $request)
    {
        list($form, $data) = $this->requisitionReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->purchaseRequisitionReport($data);

        return $this->render('PmsReportBundle:Report:purchaseRequisitionReport.html.twig', array(
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
            'form' => $form->createView(),
        ));
    }

    public function requisitionExcelAction(Request $request)
    {
        list($form, $data) = $this->requisitionReportSearchForm($request);

        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->purchaseRequisitionReport($data);

        $header_arrays = $this->headerArray();

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'RequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("J5", 'Requisition Report');
        if(!empty($purchaseRequisitionItems)){
            $index = 10;
            $count = 1;
            foreach($purchaseRequisitionItems as $budgetDaily) {

                $prNo = $budgetDaily->getPurchaseRequisition()->getId() ? $budgetDaily->getPurchaseRequisition()->getId() : "";
                $refNo = $budgetDaily->getPurchaseRequisition()->getRefNo() ? $budgetDaily->getPurchaseRequisition()->getRefNo() : "";
                $project = $budgetDaily->getPurchaseRequisition()->getProject()->getProjectName() ? $budgetDaily->getPurchaseRequisition()->getProject()->getProjectName() : "";
                $projectCategoryName = $budgetDaily->getPurchaseRequisition()->getProject()->getProjectCategory() ? $budgetDaily->getPurchaseRequisition()->getProject()->getProjectCategory()->getProjectCategoryName() : "";
                $subCategoryName = $budgetDaily->getPurchaseRequisition()->getSubCategory() ? $budgetDaily->getPurchaseRequisition()->getSubCategory()->getSubCategoryName() : "";
                $costHeader = $budgetDaily->getPurchaseRequisition()->getCostHeader() ? $budgetDaily->getPurchaseRequisition()->getCostHeader()->getTitle() : "";
                $itemName = $budgetDaily->getItem()->getItemName() ? $budgetDaily->getItem()->getItemName() : "";
                $prQty = $budgetDaily->getQuantity() ? $budgetDaily->getQuantity() : "";
                $issueDatePr = $budgetDaily->getPurchaseRequisition()->getCreatedDate() ? $budgetDaily->getPurchaseRequisition()->getCreatedDate()->format('d-m-Y') : "";
                $approvedDateProjectHead = $budgetDaily->getPurchaseRequisition()->getApprovedDateProjectHead() ? $budgetDaily->getPurchaseRequisition()->getApprovedDateProjectHead()->format('d-m-Y') : "";
                $amount = $budgetDaily->getTotalPrice() ? $budgetDaily->getTotalPrice() : 0;
                $issuedBy = $budgetDaily->getPurchaseRequisition()->getCreatedBy() ? $budgetDaily->getPurchaseRequisition()->getCreatedBy()->getUsername() : "";

                $claimedBy = $budgetDaily->getClaimedBy() ? $budgetDaily->getClaimedBy()->getUsername() : "";
                $approvedByProjectHead = $budgetDaily->getPurchaseRequisition()->getApprovedByProjectHead() ? $budgetDaily->getPurchaseRequisition()->getApprovedByProjectHead()->getUsername() : "";
                $approvedOne = $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadOne() ? $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadOne()->getUsername() : "";
                $approvedTwo = $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadTwo() ? $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadTwo()->getUsername() : "";

                $claimedDate = $budgetDaily->getClaimedDate() ? $budgetDaily->getClaimedDate()->format('Y-m-d') : "";
                $approvedDateOne = $budgetDaily->getPurchaseRequisition()->getApprovedDateCategoryHeadOne() ? $budgetDaily->getPurchaseRequisition()->getApprovedDateCategoryHeadOne()->format('d-m-Y') : "";
                $approvedDateTwo = $budgetDaily->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo() ? $budgetDaily->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo()->format('d-m-Y') : "";
                $purchaseFrom = $budgetDaily->getIsHeadOrLocal() ? $budgetDaily->getIsHeadOrLocal() . ' office' : "";

                if ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItem() == 0) {
                    $totalRequisitionItem = 1;
                } else {
                    $totalRequisitionItem = $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItem();
                }

                if ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() == 0) {
                    $totalRequisitionItemQuantity = 1;
                } else {
                    $totalRequisitionItemQuantity = $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity();
                }

                if ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 1 and $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() != 0) {
                    $approvePercentage = 33;
                } elseif ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 2 and $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() != 0) {
                    $approvePercentage = 66;
                } elseif ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3 and $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() != 0) {
                    $approvePercentage = 100;
                } else {
                    $approvePercentage = 0;
                }

                if ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() == 0) {
                    $claimPercentage = 0;
                }else{
                    if ((($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() * 100) / $totalRequisitionItem) > 100) {
                        $claimPercentage = 100;
                    } else {
                        $claimPercentage = ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() * 100) / $totalRequisitionItem;
                    }
                }

                if( ( ($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() * 100) / $totalRequisitionItemQuantity) > 100 ){
                    $poIssuedPercentage = 100;
                }else{
                    $poIssuedPercentage = ($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() * 100) / $totalRequisitionItemQuantity;
                }

                $prComment = array();
                foreach($budgetDaily->getPurchaseRequisition()->getPurchaseRequisitionComment() as $pRequisitionComment){
                    $prComment[] = $pRequisitionComment->getComment() ? $pRequisitionComment->getComment() : '';
                }
                $prComment = implode (", ", $prComment);

                if(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 2) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 3)){
                    $status = 'Created';
                }elseif(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 2) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 3)){
                    $status = 'Verified';
                }elseif(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 2) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 3)){
                    $status = 'Validated';
                }elseif(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() != 2) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() == null ) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() == 0)){
                    $status = 'Approved';
                }elseif(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() != null ) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() < $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItem()) ){
                    $status = 'PartialClaim';
                }elseif(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() != null ) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() == $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItem()) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() > $budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity()) ){
                    $status = 'InProgress';
                }elseif(($budgetDaily->getPurchaseRequisition()->getStatus() == 1) and
                    ($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3) and
                    ($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() >= $budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity()) ){
                    $status = 'PoIssued';
                }

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $prNo);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $refNo);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $projectCategoryName);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $subCategoryName);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $costHeader);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $issuedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $status);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $claimPercentage);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $poIssuedPercentage);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $approvePercentage);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $prComment);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $prQty);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $issueDatePr);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $approvedByProjectHead);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$index, $approvedDateProjectHead);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$index, $approvedOne);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$index, $approvedDateOne);
                $objPHPExcel->getActiveSheet()->setCellValue("U".$index, $approvedTwo);
                $objPHPExcel->getActiveSheet()->setCellValue("V".$index, $approvedDateTwo);
                $objPHPExcel->getActiveSheet()->setCellValue("W".$index, $claimedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("X".$index, $claimedDate);
                $objPHPExcel->getActiveSheet()->setCellValue("Y".$index, $purchaseFrom);
                $objPHPExcel->getActiveSheet()->setCellValue("Z".$index, $amount);

              //  $i = 0;
                foreach($budgetDaily->getPurchaseOrderItems() as $budgetDailyPO){
                  //  $i = $i + 1;

                    $numberPO = $budgetDailyPO->getPurchaseOrder()->getId() ? $budgetDailyPO->getPurchaseOrder()->getId() : "...";
                    $approvedThreeDate = $budgetDailyPO->getPurchaseOrder()->getApprovedThreeDate() ? $budgetDailyPO->getPurchaseOrder()->getApprovedThreeDate()->format('Y-m-d') : "00-00-00";
                    $approvedOneDate = $budgetDailyPO->getPurchaseOrder()->getApprovedOneDate() ? $budgetDailyPO->getPurchaseOrder()->getApprovedOneDate()->format('Y-m-d') : "00-00-00";
                    $approvedTwoDate = $budgetDailyPO->getPurchaseOrder()->getApprovedTwoDate() ? $budgetDailyPO->getPurchaseOrder()->getApprovedTwoDate()->format('Y-m-d') : "00-00-00";
                    $issueDate = $budgetDailyPO->getPurchaseOrder()->getCreatedDate()->format('Y-m-d') ? $budgetDailyPO->getPurchaseOrder()->getCreatedDate()->format('Y-m-d') : "...";
                    $poNonpo = $budgetDailyPO->getPurchaseOrder()->getPoNonpo()->getName() ? $budgetDailyPO->getPurchaseOrder()->getPoNonpo()->getName() : "...";

                    $createdBy = $budgetDailyPO->getPurchaseOrder()->getCreatedBy() ? $budgetDailyPO->getPurchaseOrder()->getCreatedBy()->getUsername() : "...";
                    $approvedThree = $budgetDailyPO->getPurchaseOrder()->getApprovedThree() ? $budgetDailyPO->getPurchaseOrder()->getApprovedThree()->getUsername() : "...";
                    $approvedTwo = $budgetDailyPO->getPurchaseOrder()->getApprovedTwo() ? $budgetDailyPO->getPurchaseOrder()->getApprovedTwo()->getUsername() : "...";
                    $approvedOne = $budgetDailyPO->getPurchaseOrder()->getApprovedOne() ? $budgetDailyPO->getPurchaseOrder()->getApprovedOne()->getUsername() : "...";
                    $buyer = $budgetDailyPO->getPurchaseOrder()->getBuyer() ? $budgetDailyPO->getPurchaseOrder()->getBuyer()->getUsername() : "...";

                    $vendor = $budgetDailyPO->getPurchaseOrder()->getVendor() ? $budgetDailyPO->getPurchaseOrder()->getVendor()->getVendorName() : "...";
                    $poQuantity = $budgetDailyPO->getQuantity() ? $budgetDailyPO->getQuantity() : "...";
                    $netTotal = $budgetDailyPO->getAmount() ? $budgetDailyPO->getAmount() : '...';
                    $paymentMethod = $budgetDailyPO->getPurchaseOrder()->getPaymentMethod() ? $budgetDailyPO->getPurchaseOrder()->getPaymentMethod() : '...';
                    $paymentType = $budgetDailyPO->getPurchaseOrder()->getPaymentType() ? $budgetDailyPO->getPurchaseOrder()->getPaymentType() : '...';

                    $price = $budgetDailyPO->getPrice() ? $budgetDailyPO->getPrice() : '...';

                    if($budgetDailyPO->getPurchaseOrder()->getPaymentFrom() == 1){
                        $paymentFrom = 'Local Office';
                    }else{
                        $paymentFrom = 'Head Office';
                    }

                    if($budgetDailyPO->getPurchaseOrder()->getComputationStatus() == 1){
                        $computationStatus = 'yes';
                    }else{
                        $computationStatus = 'no';
                    }

                    $poComment = array();
                    foreach($budgetDailyPO->getPurchaseOrder()->getPurchaseOrderComment() as $pOrderComment){
                        $poComment[] = $pOrderComment->getComment() ? $pOrderComment->getComment() : '';
                    }
                    $poComment = implode (", ", $poComment);

                    $objPHPExcel->getActiveSheet()->setCellValue("AA".$index, $numberPO);
                    $objPHPExcel->getActiveSheet()->setCellValue("AB".$index, $issueDate);
                    $objPHPExcel->getActiveSheet()->setCellValue("AC".$index, $paymentType);
                    $objPHPExcel->getActiveSheet()->setCellValue("AD".$index, $paymentMethod);
                    $objPHPExcel->getActiveSheet()->setCellValue("AE".$index, $paymentFrom);
                    $objPHPExcel->getActiveSheet()->setCellValue("AF".$index, $computationStatus);
                    $objPHPExcel->getActiveSheet()->setCellValue("AG".$index, $approvedThreeDate);
                    $objPHPExcel->getActiveSheet()->setCellValue("AH".$index, $poComment);
                    $objPHPExcel->getActiveSheet()->setCellValue("AI".$index, $poNonpo);
                    $objPHPExcel->getActiveSheet()->setCellValue("AJ".$index, $poQuantity);
                    $objPHPExcel->getActiveSheet()->setCellValue("AK".$index, $price);
                    $objPHPExcel->getActiveSheet()->setCellValue("AL".$index, $createdBy);
                    $objPHPExcel->getActiveSheet()->setCellValue("AM".$index, $approvedOne);
                    $objPHPExcel->getActiveSheet()->setCellValue("AN".$index, $approvedOneDate);
                    $objPHPExcel->getActiveSheet()->setCellValue("AO".$index, $approvedTwo);
                    $objPHPExcel->getActiveSheet()->setCellValue("AP".$index, $approvedTwoDate);
                    $objPHPExcel->getActiveSheet()->setCellValue("AQ".$index, $approvedThree);
                    $objPHPExcel->getActiveSheet()->setCellValue("AR".$index, $buyer);
                    $objPHPExcel->getActiveSheet()->setCellValue("AS".$index, $vendor);
                    $objPHPExcel->getActiveSheet()->setCellValue("AT".$index, $netTotal);

                        foreach($budgetDailyPO->getReceivedItems() as $receivedItem){
                            $receiveDate = $receivedItem->getReceive()->getReceivedDate()->format('Y-m-d') ? $receivedItem->getReceive()->getReceivedDate()->format('Y-m-d') : "...";
                            $grn = $receivedItem->getReceive()->getId() ? $receivedItem->getReceive()->getId() : "...";
                            $receiveBy = $receivedItem->getReceive()->getReceivedBy()->getUsername() ? $receivedItem->getReceive()->getReceivedBy()->getUsername() : "...";

                            $objPHPExcel->getActiveSheet()->setCellValue("AU".$index, $receiveDate);
                            $objPHPExcel->getActiveSheet()->setCellValue("AV".$index, $grn);
                            $objPHPExcel->getActiveSheet()->setCellValue("AW".$index, $receiveBy);

                          //  $index++;
                        }

                 //   $index++;
                 //   $count++;
                }

             //   if($i > 0){$index = $index - 1;$count = $count - 1;}

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
    public function prInprogressReportExcelAction(Request $request,$status)
    {

        $purchaseRequisitionInfo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getPrInProgressData($status,$request->query->get('month'));

        $header_arrays = array(
            'B9' => 'S/L',
            'C9' => 'PR Date',
            'D9' => 'PR No',
            'E9' => 'Project',
            'F9' => 'SubCategory',
            'G9' => 'Status',
          //  'H9' => 'Approved',
           // 'I9' => 'Claimed',

            );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'InProgressRequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PR In-progress Excel Report');
        if(!empty($purchaseRequisitionInfo)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionInfo as $list) {

              //  $approved = $this->parcentageForApproved($list);
              //  $claimed = $this->parcentageForClaim($list);

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list->getDateOfRequisition()->format('d-M-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list->getId());
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list->getProject()->getProjectName());
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list->getSubCategory()->getSubcategoryName());
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list->getStatus() ? 'in-progress':'');
              //  $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $approved);
              //  $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $claimed);

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

    public function prApprovedReportExcelAction(Request $request,$status)
    {

        $purchaseRequisitionInfo = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getPrApprovedData($status,$request->query->get('month'));

        $header_arrays = array(
            'B9' => 'S/L',
            'C9' => 'PR Date',
            'D9' => 'PR No',
            'E9' => 'Project',
            'F9' => 'SubCategory',
            'G9' => 'Status',
            //  'G9' => 'Approved',
            //  'H9' => 'Claimed',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'InProgressRequisitionReport'.'.xlsx';
        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PR Approved Excel Report');
        if(!empty($purchaseRequisitionInfo)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionInfo as $list) {

                //  $approved = $this->parcentageForApproved($list);
                // $claimed = $this->parcentageForClaim($list);

                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list->getDateOfRequisition()->format('d-M-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list->getId());
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list->getProject()->getProjectName());
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list->getSubCategory()->getSubcategoryName());
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list->getapproveStatus()?'Approved':'');
                //  $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $approved);
                // $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $claimed);

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

    /**
     * @return array
     */
    protected function headerArray()
    {
        $header_arrays = array(
            'B9' => 'PR No',
            'C9' => 'RefNo',
            'D9' => 'Project Name',
            'E9' => 'Company Name',
            'F9' => 'SubCategory',
            'G9' => 'CostHead',
            'H9' => 'IssuedBy',
            'I9' => 'Status',
            'J9' => 'Approved%',
            'K9' => 'Claimed%',
            'L9' => 'PoIssued%',
            'M9' => 'PR Comment',
            'N9' => 'Item Name',
            'O9' => 'PR Qty',
            'P9' => 'PR Issue Date',
            'Q9' => 'Verified By',
            'R9' => 'Verified Date',
            'S9' => 'Validated By',
            'T9' => 'Validated Date',
            'U9' => 'Approve By',
            'V9' => 'Approved Date',
            'W9' => 'Claimed By',
            'X9' => 'Claimed Date',
            'Y9' => 'Purchase From',
            'Z9' => 'PR Amount',

            'AA9' => 'PO No',
            'AB9' => 'PO Issue Date',
            'AC9' => 'PaymentType',
            'AD9' => 'PaymentMethod',
            'AE9' => 'PaymentFrom',
            'AF9' => 'Computation',
            'AG9' => 'Approve Date',
            'AH9' => 'PO Comment',
            'AI9' => 'Purchase Type',
            'AJ9' => 'PO Qty',
            'AK9' => 'UnitPrice',
            'AL9' => 'PO Issued By',
            'AM9' => 'VerifiedBy',
            'AN9' => 'VerifiedDate',
            'AO9' => 'ValidatedBy',
            'AP9' => 'ValidatedDate',
            'AQ9' => 'ApprovedBy',
            'AR9' => 'Buyer',
            'AS9' => 'Vendor',
            'AT9' => 'PO Amount',

            'AU9' => 'Receive Date',
            'AV9' => 'GRN No',
            'AW9' => 'Receive By'
        );
        return $header_arrays;
    }

    /**
     * @param $list
     * @return string
     */
    private function parcentageForApproved($list)
    {
        if ($list->getapproveStatus() == 1) {
            $approved = '33%';

            return $approved;
        } elseif ($list->getapproveStatus() == 2) {
            $approved = '66%';

            return $approved;
        } else {
            $approved = '100%';

            return $approved;
        }
    }
    /**
     * @param $list
     * @return string
     */
    private function parcentageForClaim($list)
    {
        if($list->getTotalRequisitionItemQuantity() == 0 and $list->status() == 6 ){
            return $claimPercentage = '100%';
        }elseif($list->getTotalRequisitionItemQuantity() == 0){
            return $claimPercentage = '0%';
        }else {
            if((($list->getTotalRequisitionItemClaimed() * 100) / $list->getTotalRequisitionItem()) > 100){
                return $claimPercentage = '100%';
            } else {

                if(($list->getTotalRequisitionItemClaimed()* 100) / $list->getTotalRequisitionItem() > 100){
                    return $claimPercentage = '100%';
                } else {
                  return   $claimPercentage = '';
                }

            }
        }
    }
}