<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PurchaseOfficerSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PurchaseOfficerReportController extends Controller
{
    public function purchaseOfficerSearchForm($request){

        $form = new PurchaseOfficerSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function purchaseOfficerAction(Request $request)
    {
        list($form, $data) = $this->purchaseOfficerSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $budgetDailies = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findPurchaseOfficerInformation($data);

        return $this->render('PmsReportBundle:Report:purchaseOfficerReport.html.twig', array(
            'budgetDailies' => $budgetDailies,
            'form' => $form->createView(),
        ));
    }

    public function purchaseOfficerExcelAction(Request $request)
    {
        list($form, $data) = $this->purchaseOfficerSearchForm($request);

        $budgetDailies = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->findPurchaseOfficerInformation($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Project',
            'C9'=>'Date',
            'D9'=>'PO No',
            'E9'=>'Buyer',
            'F9'=>'Designation',
            'G9'=>'Phone',
            'H9'=>'Remarks'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PurchaseOfficer'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Purchase Officer');
        if(!empty($budgetDailies)){
            $index = 10;
            $count = 1;
            foreach($budgetDailies as $budgetDaily){

                $project = $budgetDaily->getProject()->getProjectName() ? $budgetDaily->getProject()->getProjectName() : "";
                $date = $budgetDaily->getPurchaseOrder()->getCreatedDate()->format('Y-m-d') ? $budgetDaily->getPurchaseOrder()->getCreatedDate()->format('Y-m-d') : "";
                $poNo = $budgetDaily->getPurchaseOrder()->getId() ? $budgetDaily->getPurchaseOrder()->getId() : "";
                $buyer = $budgetDaily->getPurchaseOrder()->getBuyer()->getUsername() ? $budgetDaily->getPurchaseOrder()->getBuyer()->getUsername() : "";
                $designation = $budgetDaily->getPurchaseOrder()->getBuyer()->getProfile()->getDesignation() ? $budgetDaily->getPurchaseOrder()->getBuyer()->getProfile()->getDesignation() : "";
                $phone = $budgetDaily->getPurchaseOrder()->getBuyer()->getProfile()->getCellphone() ? $budgetDaily->getPurchaseOrder()->getBuyer()->getProfile()->getCellphone() : "";
                $remarks = $budgetDaily->getPurchaseOrder()->getComment() ? $budgetDaily->getPurchaseOrder()->getComment() : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $date);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $poNo);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $buyer);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $designation);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $phone);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $remarks);
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