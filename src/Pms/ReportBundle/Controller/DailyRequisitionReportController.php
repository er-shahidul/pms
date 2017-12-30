<?php

namespace Pms\ReportBundle\Controller;

use Pms\ReportBundle\Form\DailyRequisitionSearchType;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;

use Doctrine\ORM\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class DailyRequisitionReportController  extends Controller
{
    public function DailyRequisitionReportSearchForm($request)
    {
        $form = new DailyRequisitionSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function requisitionDailyAction(Request $request)
    {

        list($form, $data) = $this->DailyRequisitionReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseRequisitions = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getDailyPurchaseRequisitionReport($data);

        return $this->render('PmsReportBundle:Report:dailyRequisitionReport.html.twig', array(
            'purchaseRequisitions' => $purchaseRequisitions,
            'form' => $form->createView(),
        ));
    }

    public function requisitionDailyExcelAction(Request $request)
    {
        list($form, $data) = $this->DailyRequisitionReportSearchForm($request);

        $purchaseRequisitions = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getDailyPurchaseRequisitionReport($data);

        $header_arrays = $this->headerArray();

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'DailyPR'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Daily Basis PR Report');
        if(!empty($purchaseRequisitions)){
            $index = 10;
            $count = 1;
            foreach($purchaseRequisitions as $budgetDaily){

                $prNo = $budgetDaily['id'] ? $budgetDaily['id'] : "";
                $refNo = $budgetDaily['refNo'] ? $budgetDaily['refNo'] : "";
                $priority = $budgetDaily['priority'] ? $budgetDaily['priority'] : "";
                $projectName = $budgetDaily['projectName'] ? $budgetDaily['projectName'] : "";
                $isHeadOrLocal = $budgetDaily['isHeadOrLocal'] ? $budgetDaily['isHeadOrLocal'].' office' : "";
                $itemName = $budgetDaily['itemName'] ? $budgetDaily['itemName'] : "";
                $prQty = $budgetDaily['quantity'] ? $budgetDaily['quantity'] : "";
                $issueDate = $budgetDaily['createdDate']->format('Y-m-d') ? $budgetDaily['createdDate']->format('Y-m-d') : "";
                $prAmount = $budgetDaily['totalPrice'] ? $budgetDaily['totalPrice'] : 0;

                $approveDate = $budgetDaily['approvedDateCategoryHeadTwo'] ? $budgetDaily['approvedDateCategoryHeadTwo']->format('Y-m-d') : "";
                $itemType = $budgetDaily['itemType'] ? $budgetDaily['itemType'] : "";
                $category = $budgetDaily['categoryName'] ? $budgetDaily['categoryName'] : "";
                $subCategory = $budgetDaily['subCategoryName'] ? $budgetDaily['subCategoryName'] : "";
                $costHeader = $budgetDaily['title'] ? $budgetDaily['title'] : "";
                $createdBy = $budgetDaily['createdBy'] ? $budgetDaily['createdBy'] : "";
                $claimedBy = $budgetDaily['claimedBy'] ? $budgetDaily['claimedBy'] : "";
                $claimedDate = $budgetDaily['claimedDate'] ? $budgetDaily['claimedDate']->format('Y-m-d') : "";
                $verifiedBy = $budgetDaily['approvedByOne'] ? $budgetDaily['approvedByOne'] : "";
                $validatedBy = $budgetDaily['approvedByTwo'] ? $budgetDaily['approvedByTwo']  : "";
                $approvedBy = $budgetDaily['approvedByThree']  ? $budgetDaily['approvedByThree']  : "";
                $closedBy = $budgetDaily['closedBy']  ? $budgetDaily['closedBy']  : "";
                $closeRemark = $budgetDaily['closeRemark']  ? $budgetDaily['closeRemark']  : "";

                if($budgetDaily['totalRequisitionItem']== 0){
                    $totalRequisitionItem = 1;
                }else{
                    $totalRequisitionItem = $budgetDaily['totalRequisitionItem'];
                }

                if($budgetDaily['totalRequisitionItemQuantity'] == 0){
                    $totalRequisitionItemQuantity = 1;
                }else{
                    $totalRequisitionItemQuantity = $budgetDaily['totalRequisitionItemQuantity'];
                }

                if($budgetDaily['approveStatus'] == 0){
                    $status = 'created';
                }elseif($budgetDaily['approveStatus'] == 1){
                    $status = 'Verified';
                }elseif($budgetDaily['approveStatus'] == 2){
                    $status = 'validated';
                }elseif($budgetDaily['approveStatus'] == 3){
                    $status = 'Approved';
                }elseif($budgetDaily['approveStatus'] == 3 && $budgetDaily['totalRequisitionItemClaimed'] == 0){
                    $status = 'In progress';
                }elseif( (($budgetDaily['totalRequisitionItemClaimed'] * 100) / $totalRequisitionItem) > 99 && (($budgetDaily['totalOrderItemQuantity'] * 100) / $totalRequisitionItemQuantity == 0)){
                    $status = 'Complete';
                }elseif( (($budgetDaily['totalRequisitionItemClaimed'] * 100) / $totalRequisitionItem) > 99 && (($budgetDaily['totalOrderItemQuantity'] * 100) / $totalRequisitionItemQuantity < 99 ) && ((($budgetDaily['totalOrderItemQuantity'] * 100) / $totalRequisitionItemQuantity) > 1 )){
                    $status = 'Partial PO Issued';
                }elseif( (($budgetDaily['totalRequisitionItemClaimed'] * 100) / $totalRequisitionItem) > 99 && (($budgetDaily['totalOrderItemQuantity'] * 100) / $totalRequisitionItemQuantity > 99 )){
                    $status = 'PO Issued';
                }

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $prNo);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $refNo);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $issueDate);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $createdBy);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $approveDate);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $isHeadOrLocal);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $itemType);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $category);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $subCategory);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $costHeader);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $prQty);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $prAmount);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $claimedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $claimedDate);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$index, $priority);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$index, $verifiedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$index, $validatedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("U".$index, $approvedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("V".$index, $status);
                $objPHPExcel->getActiveSheet()->setCellValue("W".$index, $closedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("X".$index, $closeRemark);
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
            'A9' => 'S/L',
            'B9' => 'PR No',
            'C9' => 'PR Ref.No',
            'D9' => 'Issue Date',
            'E9' => 'Created By',
            'F9' => 'Approve Date',
            'G9' => 'Project Name',
            'H9' => 'Purchase From',
            'I9' => 'Item Name',
            'J9' => 'Item Type',
            'K9' => 'Category',
            'L9' => 'SubCategory',
            'M9' => 'Cost Header',
            'N9' => 'PR Qty',
            'O9' => 'PR Amount',
            'P9' => 'Claimed By',
            'Q9' => 'Claimed Date',
            'R9' => 'Priority',
            'S9' => 'Verified By',
            'T9' => 'Validated By',
            'U9' => 'Approved By',
            'V9' => 'Status',
            'W9' => 'Closed By',
            'X9' => 'Close Remark',
        );
        return $header_arrays;
    }

    public function requisitionDailyExcel1Action(Request $request)
    {
        list($form, $data) = $this->DailyRequisitionReportSearchForm($request);

        $purchaseRequisitions = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getDailyPurchaseRequisitionReport($data);

        $header_arrays = $this->headerArray();

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'DailyPR'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Daily Basis PR Report');
        if(!empty($purchaseRequisitions)){
            $index = 10;
            $count = 1;
            foreach($purchaseRequisitions as $budgetDaily){

                $prNo = $budgetDaily->getPurchaseRequisition()->getId() ? $budgetDaily->getPurchaseRequisition()->getId() : "";
                $priority = $budgetDaily->getPurchaseRequisition()->getPriority() ? $budgetDaily->getPurchaseRequisition()->getPriority() : "";
                $projectName = $budgetDaily->getPurchaseRequisition()->getProject()->getProjectName() ? $budgetDaily->getPurchaseRequisition()->getProject()->getProjectName() : "";
                $isHeadOrLocal = $budgetDaily->getIsHeadOrLocal() ? $budgetDaily->getIsHeadOrLocal().' office' : "";
                $itemName = $budgetDaily->getItem()->getItemName() ? $budgetDaily->getItem()->getItemName() : "";
                $prQty = $budgetDaily->getQuantity() ? $budgetDaily->getQuantity() : "";
                $issueDate = $budgetDaily->getPurchaseRequisition()->getCreatedDate()->format('Y-m-d') ? $budgetDaily->getPurchaseRequisition()->getCreatedDate()->format('Y-m-d') : "";
                $prAmount = $budgetDaily->getTotalPrice() ? $budgetDaily->getTotalPrice() : 0;

                $approveDate = $budgetDaily->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo() ? $budgetDaily->getPurchaseRequisition()->getApprovedDateCategoryHeadTwo()->format('Y-m-d') : "";
                $itemType = $budgetDaily->getItem()->getItemType()->getItemType() ? $budgetDaily->getItem()->getItemType()->getItemType() : "";
                $category = $budgetDaily->getPurchaseRequisition()->getCategory() ? $budgetDaily->getPurchaseRequisition()->getCategory()->getCategoryName() : "";
                $subCategory = $budgetDaily->getPurchaseRequisition()->getSubCategory() ? $budgetDaily->getPurchaseRequisition()->getSubCategory()->getSubCategoryName() : "";
                $costHeader = $budgetDaily->getPurchaseRequisition()->getCostHeader() ? $budgetDaily->getPurchaseRequisition()->getCostHeader()->getTitle() : "";
                $claimedBy = $budgetDaily->getClaimedBy() ? $budgetDaily->getClaimedBy()->getUsername() : "";
                $claimedDate = $budgetDaily->getClaimedDate() ? $budgetDaily->getClaimedDate()->format('Y-m-d') : "";
                $verifiedBy = $budgetDaily->getPurchaseRequisition()->getApprovedByProjectHead() ? $budgetDaily->getPurchaseRequisition()->getApprovedByProjectHead()->getUsername() : "";
                $validatedBy = $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadOne() ? $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadOne()->getUsername() : "";
                $approvedBy = $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadTwo() ? $budgetDaily->getPurchaseRequisition()->getApprovedByCategoryHeadTwo()->getUsername() : "";

                if($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItem() == 0){
                    $totalRequisitionItem = 1;
                }else{
                    $totalRequisitionItem = $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItem();
                }

                if($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity() == 0){
                    $totalRequisitionItemQuantity = 1;
                }else{
                    $totalRequisitionItemQuantity = $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemQuantity();
                }

                if($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 0){
                    $status = 'created';
                }elseif($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 1){
                    $status = 'Verified';
                }elseif($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 2){
                    $status = 'validated';
                }elseif($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3){
                    $status = 'Approved';
                }elseif($budgetDaily->getPurchaseRequisition()->getApproveStatus() == 3 && $budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() == 0){
                    $status = 'In progress';
                }elseif( (($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() * 100) / $totalRequisitionItem) > 99 && (($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() * 100) / $totalRequisitionItemQuantity == 0)){
                    $status = 'Complete';
                }elseif( (($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() * 100) / $totalRequisitionItem) > 99 && (($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() * 100) / $totalRequisitionItemQuantity < 99 ) && ((($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() * 100) / $totalRequisitionItemQuantity) > 1 )){
                    $status = 'Partial PO Issued';
                }elseif( (($budgetDaily->getPurchaseRequisition()->getTotalRequisitionItemClaimed() * 100) / $totalRequisitionItem) > 99 && (($budgetDaily->getPurchaseRequisition()->getTotalOrderItemQuantity() * 100) / $totalRequisitionItemQuantity > 99 )){
                    $status = 'PO Issued';
                }

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $prNo);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $issueDate);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $approveDate);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $isHeadOrLocal);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $itemType);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $category);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $subCategory);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $costHeader);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $prQty);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $prAmount);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $claimedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $claimedDate);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $priority);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $verifiedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$index, $validatedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$index, $approvedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$index, $status);
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
}