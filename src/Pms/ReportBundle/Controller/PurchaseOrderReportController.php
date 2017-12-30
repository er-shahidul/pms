<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PurchaseOrderReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PurchaseOrderReportController extends Controller
{
    public function purchaseOrderSearchForm($request){

        $form = new PurchaseOrderReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function purchaseOrderReportAction(Request $request)
    {
        list($form, $data) = $this->purchaseOrderSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $poReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseOrderReportReport($data);
//var_dump($poReport);die;
        return $this->render('PmsReportBundle:Report:purchaseOrderReport.html.twig', array(
            'poReport' => $poReport,
            'formSearch' => $form->createView(),
        ));
    }

    public function purchaseOrderReportExcelAction(Request $request)
    {
        list($form, $data) = $this->purchaseOrderSearchForm($request);

        $poReports = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->getPurchaseOrderReportReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item',
            'C9'=>'Unit',
            'D9'=>'Item type',
            'E9'=>'Category Name',
            'F9'=>'Sub Category Name',
            'G9'=>'Project Name',
            'H9'=>'Company Name',
            'I9'=>'Project Area',
            'J9'=>'Unit Price',
            'K9'=>'Po No',
            'L9'=>'Po Ref No',
            'M9'=>'Po Qty',
            'N9'=>'Po Date',
            'O9'=>'Month',
            'P9'=>'Issued By',
            'Q9'=>'Buyer Name',
            'R9'=>'Vendor Name',
            'S9'=>'Purchase Type',
            'T9'=>'PO Amount',
            'U9'=>'Close/Cancel Qty',
            'V9'=>'Close/Cancel By',
            'W9'=>'Close/Cancel Remarks',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PO'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'PO Report');
        if(!empty($poReports)){
            $index = 11;
            $count = 1;
            foreach($poReports as $poReport){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $poReport['itemName']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $poReport['itemUnit']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $poReport['itemType']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $poReport['categoryName']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $poReport['subCategoryName']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $poReport['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $poReport['companyType'] ? $poReport['companyType']:'' );
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $poReport['areaName']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $poReport['purchaseOrderItem']->getPrice());
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $poReport['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $poReport['refNo']);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $poReport['purchaseOrderItem']->getQuantity());
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $poReport['createdDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $poReport['createdDate']->format('Y-M'));
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $poReport['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $poReport['buyerName']);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$index, $poReport['vendorName']);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$index, $poReport['purchaseTypeName']);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$index, $poReport['netTotal']);
                $objPHPExcel->getActiveSheet()->setCellValue("U".$index, $poReport['closeQty'] ? $poReport['closeQty']: 0);
                $objPHPExcel->getActiveSheet()->setCellValue("V".$index, $poReport['closeBy'] ? $poReport['closeBy']: '');
                $objPHPExcel->getActiveSheet()->setCellValue("W".$index, $poReport['remark'] ? $poReport['remark']: '');

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