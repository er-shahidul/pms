<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\BetweenTwoDateSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class UserActivitiesPrCreatorController extends Controller
{
    public function userActivitiesSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function userActivitiesPrCreatorAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseRequisitions = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->userPrCreatorListReport($data);

        foreach($purchaseRequisitions as $key => $rows ){
            $index = $rows['id'] . $rows['username'];
            $purchaseRequisitions[$key][$index] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getTotalProjectWiseRequisition($rows['id'], $data);
        }

        return $this->render('PmsReportBundle:Report:userActivitiesPrCreatorReport.html.twig', array(
            'form' => $form->createView(),
            'purchaseRequisitions' => $purchaseRequisitions,
        ));
    }

    public function userActivitiesPrCreatorExcelAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $purchaseRequisitions = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->userPrCreatorListReport($data);

        foreach($purchaseRequisitions as $key => $rows ){
            $index = $rows['id'] . $rows['username'];
            $purchaseRequisitions[$key][$index] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getTotalProjectWiseRequisition($rows['id'], $data);
        }

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Name',
            'C9'=>'Project Name',
            'D9'=>'Total Requisition',
            'E9'=>'Total Requisition Issued',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'userActivitiesPrCreator'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'Pr Creator List');

        if(!empty($purchaseRequisitions)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitions as $purchaseRequisition){

                $username = $purchaseRequisition['username'] ? $purchaseRequisition['username'] : "";
                $projectName = $purchaseRequisition['projectName'] ? $purchaseRequisition['projectName'] : "";
                $prTotal = $purchaseRequisition[$purchaseRequisition['id'].$purchaseRequisition['username']] ? $purchaseRequisition[$purchaseRequisition['id'].$purchaseRequisition['username']] : "";
                $prIssued = $purchaseRequisition['totalPr'] ? $purchaseRequisition['totalPr'] : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $username);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $prTotal);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $prIssued);

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