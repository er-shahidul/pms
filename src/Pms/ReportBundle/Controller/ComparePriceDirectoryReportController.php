<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\ComparePriceDirectorySearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class ComparePriceDirectoryReportController extends Controller
{
    public function comparePriceDirectorySearchForm($request)
    {
        $form = new ComparePriceDirectorySearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function comparePriceDirectoryAction(Request $request)
    {
        list($form, $data) = $this->comparePriceDirectorySearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $compareDirectoryReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getComparePriceDirectory($data);
        return $this->render('PmsReportBundle:Report:comparePriceDirectoryReport.html.twig', array(
            'compareDirectoryReport' => $compareDirectoryReport,
            'form' => $form->createView(),
        ));
    }

    public function comparePriceDirectoryExcelAction(Request $request)
    {
        list($form, $data) = $this->comparePriceDirectorySearchForm($request);

        $compareDirectoryReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getComparePriceDirectory($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'Sl',
            'B9'=>'Project Name',
            'C9'=>'Item Name',
            'D9'=>'Buyer',
            'E9'=>'Vendor',
            'F9'=>'Date',
            'G9'=>'Po No',
            'H9'=>'Brand Name',
            'I9'=>'Price'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PriceDirectory'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("F5", 'Price Directory');
        if(!empty($compareDirectoryReport)){
            $index = 10;
            $count = 1;
            foreach($compareDirectoryReport as $itemFromPurchase){

                $itemName = $itemFromPurchase->getItem() ? $itemFromPurchase->getItem()->getItemName() : "";
                $projectName = $itemFromPurchase->getProject()->getProjectName() ? $itemFromPurchase->getProject()->getProjectName() : "";
                $price = $itemFromPurchase->getPrice() ? $itemFromPurchase->getPrice() : "";
                $vendorName = $itemFromPurchase->getPurchaseOrder()->getVendor() ? $itemFromPurchase->getPurchaseOrder()->getVendor()->getVendorName() : "";
                $buyerName = $itemFromPurchase->getPurchaseOrder()->getBuyer() ? $itemFromPurchase->getPurchaseOrder()->getBuyer()->getUsername() : "";
                $brand = $itemFromPurchase->getBrand() ? $itemFromPurchase->getBrand() : "";
                $createdDate = $itemFromPurchase->getPurchaseOrder()->getCreatedDate() ? $itemFromPurchase->getPurchaseOrder()->getCreatedDate()->format('d/m/Y') : "";
                $poNo = $itemFromPurchase->getPurchaseOrder()->getId() ? $itemFromPurchase->getPurchaseOrder()->getId() : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $buyerName);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $createdDate);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $poNo);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $brand);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $price);

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