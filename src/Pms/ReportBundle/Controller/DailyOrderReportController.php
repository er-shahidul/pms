<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PurchaseOrderDailySearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class DailyOrderReportController extends Controller
{
    public function dailyOrderSearchForm($request)
    {
        $form = new PurchaseOrderDailySearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function dailyOrderAction(Request $request)
    {
        list($form, $data) = $this->dailyOrderSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $dailyPoReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getDailyPoReport($data);
        return $this->render('PmsReportBundle:Report:dailyOrderReport.html.twig', array(
            'dailyPoReport' => $dailyPoReport,
            'form' => $form->createView(),
        ));
    }

    public function dailyOrderExcelAction(Request $request)
    {
        list($form, $data) = $this->dailyOrderSearchForm($request);
        $dailyPoReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getDailyPoReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'PO',
            'C9'=>'PR',
            'D9'=>'Project Name',
            'E9'=>'Vendor',
            'F9'=>'Purchase Type',
            'G9'=>'Payment Type',
            'H9'=>'payment From',
            'I9'=>'payment Method',
            'J9'=>'PO Amount',
            'K9'=>'Created By',
            'L9'=>'Verified By',
            'M9'=>'Validated By',
            'N9'=>'Approved By',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'DailyPO'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Daily Basis PO Report');
        if(!empty($dailyPoReport)){
            $index = 11;
            $count = 1;
            foreach($dailyPoReport as $itemUse){

                $poId = $itemUse['poId'] ? $itemUse['poId'] : "";
                $prId = $itemUse['prId'] ? $itemUse['prId'] : "";
                $projectName = $itemUse['projectName'] ? $itemUse['projectName'] : "";
                $vendorName= $itemUse['vendorName'] ? $itemUse['vendorName'] : "";
                $ptName= $itemUse['ptName'] ? $itemUse['ptName'] : "";
                $paymentType= $itemUse['paymentType'] ? $itemUse['paymentType'] : "";
                $paymentFrom= $itemUse['paymentFrom'] ? $itemUse['paymentFrom'] : "";
                $paymentMethod= $itemUse['paymentMethod'] ? $itemUse['paymentMethod'] : "";
                $netTotal= $itemUse['netTotal'] ? $itemUse['netTotal'] : "";
                $createdBy= $itemUse['createdBy'] ? $itemUse['createdBy'] : "";
                $verifiedBy= $itemUse['verifiedBy'] ? $itemUse['verifiedBy'] : "";
                $validateBy= $itemUse['validateBy'] ? $itemUse['validateBy'] : "";
                $approvedBy= $itemUse['approvedBy'] ? $itemUse['approvedBy'] : "";

                if($paymentFrom == 1){ $paymentFrom = 'Local office';}elseif($paymentFrom == 2){ $paymentFrom = 'Head office'; }

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $poId);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $prId);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $ptName);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $paymentType);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $paymentFrom);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $paymentMethod);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $netTotal);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $createdBy);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $verifiedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $validateBy);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $approvedBy);

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