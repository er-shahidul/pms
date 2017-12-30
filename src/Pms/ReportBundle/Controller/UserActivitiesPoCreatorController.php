<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\Common\Util\Debug;
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
class UserActivitiesPoCreatorController extends Controller
{
    public function userActivitiesSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function userActivitiesPoCreatorAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseOrders = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->userPoCreatorListReport($data);

        foreach($purchaseOrders as $key => $rows ){
            $index =$rows['username'];
            $purchaseOrders[$key][$index] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getTotalPOCount($data);
        }

        return $this->render('PmsReportBundle:Report:userActivitiesPoCreatorReport.html.twig', array(
            'form' => $form->createView(),
            'purchaseOrders' => $purchaseOrders,
        ));
    }

    public function userActivitiesPoCreatorExcelAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $purchaseOrders = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->userPoCreatorListReport($data);

        foreach($purchaseOrders as $key => $rows ){
            $index = $rows['username'];
            $purchaseOrders[$key][$index] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getTotalPOCount($data);
        }
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Name',
            'C9'=>'Total Order ',
            'D9'=>'Total Order Issued',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'userActivitiesPoCreate'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'Po Creator List');

        if(!empty($purchaseOrders)){
            $index = 11;
            $count = 1;
            foreach($purchaseOrders as $purchaseOrder){

                $username = $purchaseOrder['username'] ? $purchaseOrder['username'] : "";
                $totalPo = $purchaseOrder[$purchaseOrder['username']] ? $purchaseOrder[$purchaseOrder['username']] : "";
                $poIssued = $purchaseOrder['totalPoUserWise'] ? $purchaseOrder['totalPoUserWise'] : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $username);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $totalPo);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $poIssued);

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