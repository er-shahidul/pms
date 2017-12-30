<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\AccountSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Report controller.
 *
 */
class AccountReportController extends Controller
{
    public function accountReportSearchForm($request)
    {
        $form = new AccountSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function accountReportAction(Request $request)
    {
        list($form, $data) = $this->accountReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $accountReportsAllCriteria = $this
            ->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->accountReport($data);

        return $this->render('PmsReportBundle:Report:accountReport.html.twig',array(
            'form' => $form->createView(),
            'accountReportsAllCriterias' => $accountReportsAllCriteria
        ));
    }

    public function getCcnByProjectIdAction(){

        $em  = $this->getDoctrine()->getManager();
        if($_POST['project_id']){
            $Ccn = $em->getRepository('PmsSettingBundle:Project')->find($_POST['project_id']);
            $ccn = $Ccn->getcostCenterNumber();
        } else {
            $ccn = '';
        }

        $response = new Response(json_encode($ccn));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function accountReportExcelAction(Request $request)
    {
        $searchType = new AccountSearchType();
        $data       = $request->get($searchType->getName());

        $accountReportsAllCriteria = $this
            ->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->accountReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Po Number',
            'C9'=>'PO Date',
            'D9'=>'Po amount',
            'E9'=>'GRN no',
            'F9'=>'GRN date',
            'G9'=>'Bill amount'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'Acc'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Account Report');
        if(!empty($accountReportsAllCriteria)){
            $index = 11;
            $count = 1;
            foreach($accountReportsAllCriteria as $accountReport){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $accountReport['id']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $accountReport['createdDate']->format('y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $accountReport['netTotal']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $accountReport['receiveId']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $accountReport['receivedDate']->format('y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, '');
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