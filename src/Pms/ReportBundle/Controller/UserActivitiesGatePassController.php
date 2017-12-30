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
class UserActivitiesGatePassController extends Controller
{
    public function userActivitiesSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function userActivitiesGatePassAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $deliveries = $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery')->userDeliveryListReport($data);

        foreach($deliveries as $key => $rows ){
            $index = $rows['id'] . $rows['username'];
            $deliveries[$key][$index] = $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery')->getDeliveryCount($rows['id'], $data);
        }

        return $this->render('PmsReportBundle:Report:userActivitiesGatePassReport.html.twig', array(
            'form' => $form->createView(),
            'deliveries' => $deliveries,
        ));
    }

    public function userActivitiesGatePassExcelAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $deliveries = $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery')->userDeliveryListReport($data);

        foreach($deliveries as $key => $rows ){
            $index = $rows['id'] . $rows['username'];
            $deliveries[$key][$index] = $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery')->getDeliveryCount($rows['id'], $data);
        }

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Name',
            'C9'=>'Role',
            'D9'=>'Project Name',
            'E9'=>'Total No of Gate Pass',
            'F9'=>'Gate Pass  Issued',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'userActivitiesGetPass'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'Get Pass List');

        if(!empty($deliveries)){
            $index = 11;
            $count = 1;
            foreach($deliveries as $delivery){

                $username = $delivery['username'] ? $delivery['username'] : "";
                $role = $delivery['name'] ? $delivery['name'] : "";
                $projectName = $delivery['projectName'] ? $delivery['projectName'] : "";
                $totalGetPass = $delivery[$delivery['id'].$delivery['username']] ? $delivery[$delivery['id'].$delivery['username']] : "";
                $prIssued = $delivery['totalD'] ? $delivery['totalD'] : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $username);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $role);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $totalGetPass);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $prIssued);

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