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
class UserActivitiesClaimController extends Controller
{
    public function userActivitiesSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function userActivitiesClaimAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->userClaimListReport($data);

        foreach($purchaseRequisitionItems as $key => $rows ){
            $index = $rows['id'] . $rows['username'];
            $purchaseRequisitionItems[$key][$index] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getTotalProjectWiseRequisitionItem($rows['id'], $data);
        }

        return $this->render('PmsReportBundle:Report:userActivitiesClaimReport.html.twig', array(
            'form' => $form->createView(),
            'purchaseRequisitionItems' => $purchaseRequisitionItems,
        ));
    }

    public function userActivitiesClaimExcelAction(Request $request)
    {

        list($form, $data) = $this->userActivitiesSearchForm($request);

        $purchaseRequisitionItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->userClaimListReport($data);

        foreach($purchaseRequisitionItems as $key => $rows ){
            $index = $rows['id'] . $rows['username'];
            $purchaseRequisitionItems[$key][$index] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getTotalProjectWiseRequisitionItem($rows['id'], $data);
        }

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Name',
//            'C9'=>'Role',
            'C9'=>'Project Name',
            'D9'=>'Total Requisition Item',
            'E9'=>'Total Claimed Item',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'userActivitiesClaim'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'Claim List');

        if(!empty($purchaseRequisitionItems)){
            $index = 11;
            $count = 1;
            foreach($purchaseRequisitionItems as $purchaseRequisitionItem){

                $username = $purchaseRequisitionItem['username'] ? $purchaseRequisitionItem['username'] : "";
//                $role = $purchaseRequisitionItem['name'] ? $purchaseRequisitionItem['name'] : "";
                $projectName = $purchaseRequisitionItem['projectName'] ? $purchaseRequisitionItem['projectName'] : "";
                $prTotal = $purchaseRequisitionItem[$purchaseRequisitionItem['id'].$purchaseRequisitionItem['username']] ? $purchaseRequisitionItem[$purchaseRequisitionItem['id'].$purchaseRequisitionItem['username']] : "";
                $prIssued = $purchaseRequisitionItem['totalPri'] ? $purchaseRequisitionItem['totalPri'] : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $username);
//                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $role);
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