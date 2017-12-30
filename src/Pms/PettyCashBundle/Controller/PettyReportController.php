<?php

namespace Pms\PettyCashBundle\Controller;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;

use Pms\CoreBundle\Controller\BaseController;
use Pms\PettyCashBundle\Entity\Transaction;
use Pms\PettyCashBundle\Form\PettyCashReportSearchType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;


class PettyReportController extends BaseController
{
    public function index1Action(Request $request)
    {
        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $allTransactions = $this->getDoctrine()
                              ->getRepository('PmsPettyCashBundle:Transaction')
                              ->getAllTransaction($data);

        return $this->render('PmsPettyCashBundle:Report:pettyCashReport.html.twig',array(
            'form' => $form->createView(),
            'transactions' =>''
            ));
    } 
    public function indexAction(Request $request)
    {
        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $pettyCashReports = $this->getDoctrine()
                              ->getRepository('PmsPettyCashBundle:TransactionHistory')
                              ->getPettyCashReport($data);

        $monthlyTotalCash = $this->getDoctrine()->getRepository('PmsPettyCashBundle:TransactionHistory')->totalCashTransaction($data);
        $monthlyTotalInvoice = $this->getDoctrine()->getRepository('PmsPettyCashBundle:TransactionHistory')->totalInvoiceTransaction($data);

        return $this->render('PmsPettyCashBundle:Report:pettyCashReport.html.twig',array(
            'form' => $form->createView(),
            'transactions' =>$pettyCashReports,
            'monthlyTotalCash' => $monthlyTotalCash,
            'monthlyTotalInvoice' => $monthlyTotalInvoice
            ));
    }

    public function paymentCompanyReportSearchForm($request)
    {
        $form = new PettyCashReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }
    public function pettyExcelReportAction(Request $request)
    {
        $form = new PettyCashReportSearchType();

        $data = $request->get($form->getName());

        $pettyCashReports = $this->getDoctrine()
            ->getRepository('PmsPettyCashBundle:TransactionHistory')
            ->getPettyCashReportExcel($data);

        $monthlyTotalCash = $this->getDoctrine()->getRepository('PmsPettyCashBundle:TransactionHistory')->totalCashTransaction($data);
        $monthlyTotalInvoice = $this->getDoctrine()->getRepository('PmsPettyCashBundle:TransactionHistory')->totalInvoiceTransaction($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Start Date',
            'C9'=>'Given Amount',
            'D9'=>'Description',
            'E9'=>'From',
            'F9'=>'To',
            'G9'=>'Adjustment Amount',
            'H9'=>'From',
            'I9'=>'To',
            'J9'=>'Due Amount',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'Petty'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Petty Cash  Report');

        $objPHPExcel->getActiveSheet()->setCellValue("B6", 'Monthly Total Transaction');
        $objPHPExcel->getActiveSheet()->setCellValue("C6", 'Monthly Cash Amount');
        $objPHPExcel->getActiveSheet()->setCellValue("D6", 'Monthly Invoice Amount');

        $objPHPExcel->getActiveSheet()->setCellValue("B7", $monthlyTotalCash + $monthlyTotalInvoice);
        $objPHPExcel->getActiveSheet()->setCellValue("C7", $monthlyTotalCash);
        $objPHPExcel->getActiveSheet()->setCellValue("D7", $monthlyTotalInvoice);


        if(!empty($pettyCashReports)){
            $index = 11;
            $count = 1;
            foreach($pettyCashReports as $pettyCashReport){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $pettyCashReport['createdDate'] ? $pettyCashReport['createdDate']->format('Y-m-d'):' ');
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $pettyCashReport['transactionAmount'] ? $pettyCashReport['transactionAmount'] :'' );
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $pettyCashReport['description']?$pettyCashReport['description']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $pettyCashReport['transactFrom']?$pettyCashReport['transactFrom']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $pettyCashReport['transactTo']?$pettyCashReport['transactTo']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $pettyCashReport['transactionHistoryAmount'] ? $pettyCashReport['transactionHistoryAmount'] :'' );
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $pettyCashReport['transactTo']?$pettyCashReport['transactTo']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $pettyCashReport['transactFrom']?$pettyCashReport['transactFrom']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, ($pettyCashReport['transactionAmount'] - $pettyCashReport['transactionHistoryAmount']));

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
