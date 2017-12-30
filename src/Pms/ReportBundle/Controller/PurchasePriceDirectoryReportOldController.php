<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use mysqli;
use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PriceDirectoryOldSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PurchasePriceDirectoryReportOldController extends Controller
{
    public function purchasePriceDirectoryOldSearchForm($request)
    {
        $form = new PriceDirectoryOldSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function purchasePriceDirectoryOldAction(Request $request)
    {
//        $servername = "localhost";
//        $username = "root";
//        $password = "1";
//        $dbname = "pms";
//        $conn = new mysqli($servername, $username, $password, $dbname);
//
//        if ($conn->connect_error) {
//            die("Connection failed: " . $conn->connect_error);
//        }
//
//        $forLoops = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getAll();
//
//        foreach($forLoops as $itemFromPurchase){
//
//            $itemName = $itemFromPurchase->getItem() ? $itemFromPurchase->getItem()->getItemName() : "";
//            $categoryName = $itemFromPurchase->getPurchaseRequisitionItem()->getPurchaseRequisition()->getCategory() ? $itemFromPurchase->getPurchaseRequisitionItem()->getPurchaseRequisition()->getCategory()->getCategoryName() : "";
//            $itemType = $itemFromPurchase->getItem()->getItemType() ? $itemFromPurchase->getItem()->getItemType()->getItemType() : "";
//            $itemUnit = $itemFromPurchase->getItem()->getItemUnit() ? $itemFromPurchase->getItem()->getItemUnit() : "";
//            $areaName = $itemFromPurchase->getProject()->getProjectArea() ? $itemFromPurchase->getProject()->getProjectArea()->getAreaName() : "";
//            $projectName = $itemFromPurchase->getProject()->getProjectName() ? $itemFromPurchase->getProject()->getProjectName() : "";
//            $price = $itemFromPurchase->getPrice() ? $itemFromPurchase->getPrice() : "";
//            $vendorName = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getVendorName() : "";
//            $vendorAddress = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getVendorAddress() : "";
//            $contactPerson = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getContractPerson() : "";
//
//            $poRefNo = $itemFromPurchase->getPurchaseOrder()->getRefNo() ? $itemFromPurchase->getPurchaseOrder()->getRefNo() : "";
//            $poQty = $itemFromPurchase->getPurchaseOrder()->getTotalOrderItemQuantity() ? $itemFromPurchase->getPurchaseOrder()->getTotalOrderItemQuantity() : "";
//            $buyerName = $itemFromPurchase->getPurchaseOrder()->getBuyer() ? $itemFromPurchase->getPurchaseOrder()->getBuyer()->getUsername() : "";
//            $purchaseType = $itemFromPurchase->getPurchaseOrder()->getPoNonpo() ? $itemFromPurchase->getPurchaseOrder()->getPoNonpo()->getName() : "";
//            $brand = $itemFromPurchase->getBrand() ? $itemFromPurchase->getBrand() : "";
//            $createdDate = $itemFromPurchase->getPurchaseOrder()->getCreatedDate() ? $itemFromPurchase->getPurchaseOrder()->getCreatedDate()->format('Y-m-d H:i:s') : "";
//            $approveStatus        = $itemFromPurchase->getPurchaseOrder()->getApproveStatus() ? $itemFromPurchase->getPurchaseOrder()->getApproveStatus(): '';
//
//            $sql = "INSERT INTO price_directory_from_old
//                                (id, categoryName, approveStatus,
//                                itemType, itemName, itemUnit,
//                                price, brand, createdDate,
//                                areaName, projectName, vendorName,
//                                vendorAddress, contractPerson, refNo,
//                                totalOrderItemQuantity, buyer, poNonpo)
//                    VALUES (null,'$categoryName' ,'$approveStatus' ,'$itemType' ,
//                    '$itemName' ,'$itemUnit' ,'$price' ,'$brand' ,'$createdDate' ,'$areaName' ,
//                    '$projectName' , '$vendorName' ,'$vendorAddress' ,'$contactPerson' ,'$poRefNo' ,
//                    '$poQty' ,'$buyerName' ,'$purchaseType')";
//
//            if ($conn->query($sql) === TRUE) {
//                echo "New record created successfully";
//            } else {
//                echo "Error: " . $sql . "<br>" . $conn->error;
//            }
//
//        }
//
//
//        $conn->close();
//        die;
        list($form, $data) = $this->purchasePriceDirectoryOldSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $priceDirectoryReport = $this->paginate($this->getDoctrine()->getRepository('PmsReportBundle:PriceDirectoryFromOld')->getPurchaseOrderPriceDirectoryOld($data));
        return $this->render('PmsReportBundle:Report:purchasePriceDirectoryOldReport.html.twig', array(
            'priceDirectoryReport' => $priceDirectoryReport,
            'form' => $form->createView(),
        ));
    }

    public function purchasePriceDirectoryOldExcelAction(Request $request)
    {
        list($form, $data) = $this->purchasePriceDirectoryOldSearchForm($request);

        $priceDirectoryReport = $this->paginateExcel($this->getDoctrine()->getRepository('PmsReportBundle:PriceDirectoryFromOld')->getPurchaseOrderPriceDirectoryOld($data));

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Category Name',
            'C9'=>'Item Type',
            'D9'=>'Item Name',
            'E9'=>'UM',
            'F9'=>'Area',
            'G9'=>'Project Name',
            'H9'=>'Per Unit Rate',
            'I9'=>'Supplier',
            'J9'=>'Address',
            'K9'=>'Contact Person',
            'L9'=>'PO Ref No',
            'M9'=>'PO Qty',
            'N9'=>'Buyer Name',
            'O9'=>'Purchase Type',
            'P9'=>'Brand',
            'Q9'=>'Date',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PriceDirectory'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("F5", 'Price Directory Old');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        if(!empty($priceDirectoryReport)){
            $index = 10;
            $count = 1;
            foreach($priceDirectoryReport as $itemFromPurchase){

                $itemName = $itemFromPurchase->getItemName() ? $itemFromPurchase->getItemName() : "";
                $categoryName = $itemFromPurchase->getCategoryName() ? $itemFromPurchase->getCategoryName() : "";
                $itemType = $itemFromPurchase->getItemType() ? $itemFromPurchase->getItemType() : "";
                $itemUnit = $itemFromPurchase->getItemUnit() ? $itemFromPurchase->getItemUnit() : "";
                $areaName = $itemFromPurchase->getAreaName() ? $itemFromPurchase->getAreaName() : "";
                $projectName = $itemFromPurchase->getProjectName() ? $itemFromPurchase->getProjectName() : "";
                $price = $itemFromPurchase->getPrice() ? $itemFromPurchase->getPrice() : "";
                $vendorName = $itemFromPurchase->getVendorName() ? $itemFromPurchase->getVendorName() : "";
                $vendorAddress = $itemFromPurchase->getVendorAddress() ? $itemFromPurchase->getVendorAddress() : "";
                $contactPerson = $itemFromPurchase->getContractPerson() ? $itemFromPurchase->getContractPerson() : "";

                $poRefNo = $itemFromPurchase->getRefNo() ? $itemFromPurchase->getRefNo() : "";
                $poQty = $itemFromPurchase->getTotalOrderItemQuantity() ? $itemFromPurchase->getTotalOrderItemQuantity() : "";
                $buyerName = $itemFromPurchase->getBuyer() ? $itemFromPurchase->getBuyer() : "";
                $purchaseType = $itemFromPurchase->getPoNonpo() ? $itemFromPurchase->getPoNonpo() : "";
                $brand = $itemFromPurchase->getBrand() ? $itemFromPurchase->getBrand() : "";
                $createdDate = $itemFromPurchase->getCreatedDate() ? $itemFromPurchase->getCreatedDate()->format('d/m/Y') : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $categoryName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $itemType);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $itemUnit);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $areaName);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $price);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $vendorAddress);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $contactPerson);

                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $poRefNo);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $poQty);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $buyerName);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $purchaseType);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $brand);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $createdDate);


                $index++;
                $count++;
            }
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

    public function paginateExcel($dql)
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
            15350/*limit per page*/
        );

        return $value;
    }
}