<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PriceDirectorySearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PurchasePriceDirectoryReportController extends Controller
{
    public function purchasePriceDirectorySearchForm($request){

        $form = new PriceDirectorySearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function purchasePriceDirectoryAction(Request $request)
    {

        list($form, $data) = $this->purchasePriceDirectorySearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $priceDirectoryReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseOrderPriceDirectory($data);
        return $this->render('PmsReportBundle:Report:purchasePriceDirectoryReport.html.twig', array(
            'priceDirectoryReport' => $priceDirectoryReport,
            'form' => $form->createView(),
        ));
    }

    public function purchasePriceDirectoryExcelAction(Request $request)
    {
        list($form, $data) = $this->purchasePriceDirectorySearchForm($request);
        $purchasePriceDirectoryReports = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->getPurchaseOrderPriceDirectory($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Category Name',
            'C9'=>'Item Type',
            'D9'=>'Item Name',
            'E9'=>'UM',
            'F9'=>'Quantity',
            'G9'=>'Per Unit Rate',
            'H9'=>'Area',
            'I9'=>'Project Name',
            'J9'=>'Supplier',
            'K9'=>'Address',
            'L9'=>'Contact Person',
            'M9'=>'PO No',
            'N9'=>'PO Ref No',
            'O9'=>'Issued By',
            'P9'=>'Purchase Type',
            'Q9'=>'Brand',
            'R9'=>'Date',
            'S9'=>'Month',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PriceDirectory'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("F5", 'Price Directory');
        if(!empty($purchasePriceDirectoryReports)){
            $index = 10;
            $count = 1;
            foreach($purchasePriceDirectoryReports as $itemFromPurchase){

                $itemName = $itemFromPurchase->getItem() ? $itemFromPurchase->getItem()->getItemName() : "";
                $categoryName = $itemFromPurchase->getPurchaseRequisitionItem()->getPurchaseRequisition()->getCategory() ? $itemFromPurchase->getPurchaseRequisitionItem()->getPurchaseRequisition()->getCategory()->getCategoryName() : "";
                $itemType = $itemFromPurchase->getItem()->getItemType() ? $itemFromPurchase->getItem()->getItemType()->getItemType() : "";
                $itemUnit = $itemFromPurchase->getItem()->getItemUnit() ? $itemFromPurchase->getItem()->getItemUnit() : "";
                $areaName = $itemFromPurchase->getProject()->getProjectArea() ? $itemFromPurchase->getProject()->getProjectArea()->getAreaName() : "";
                $projectName = $itemFromPurchase->getProject()->getProjectName() ? $itemFromPurchase->getProject()->getProjectName() : "";
                $quantity = $itemFromPurchase->getQuantity() ? $itemFromPurchase->getQuantity() : "";
                $price = $itemFromPurchase->getPrice() ? $itemFromPurchase->getPrice() : "";
                $vendorName = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getVendorName() : "";
                $vendorAddress = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getVendorAddress() : "";
                $contactPerson = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getContractPerson() : "";

                $poNo = $itemFromPurchase->getPurchaseOrder()->getTotalOrderItemQuantity() ? $itemFromPurchase->getPurchaseOrder()->getTotalOrderItemQuantity() : "";
                $poRefNo = $itemFromPurchase->getPurchaseOrder()->getRefNo() ? $itemFromPurchase->getPurchaseOrder()->getRefNo() : "";
                $createdBy = $itemFromPurchase->getPurchaseOrder()->getCreatedBy() ? $itemFromPurchase->getPurchaseOrder()->getCreatedBy()->getUsername() : "";
                $purchaseType = $itemFromPurchase->getPurchaseOrder()->getPoNonpo() ? $itemFromPurchase->getPurchaseOrder()->getPoNonpo()->getName() : "";
                $brand = $itemFromPurchase->getBrand() ? $itemFromPurchase->getBrand() : "";
                $createdDate = $itemFromPurchase->getPurchaseOrder()->getCreatedDate() ? $itemFromPurchase->getPurchaseOrder()->getCreatedDate()->format('m/d/Y') : "";
                $month = $itemFromPurchase->getPurchaseOrder()->getCreatedDate() ? $itemFromPurchase->getPurchaseOrder()->getCreatedDate()->format('F') : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $categoryName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $itemType);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $itemUnit);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $quantity);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, number_format($price, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $areaName);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $vendorAddress);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $contactPerson);

                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $poNo);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $poRefNo);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $createdBy);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $purchaseType);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $brand);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$index, $createdDate);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$index, $month);

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $index++;
                $count++;
            }
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
    }

    public function excelSheetSet($header_arrays)
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
}