<?php

namespace Pms\CoreBundle\Controller\Report;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\CoreBundle\Form\SearchForm\PaymentReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PaymentReportController extends Controller
{
    public function paymentReportSearchForm($request)
    {
        $form = new PaymentReportSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function paymentReportAction(Request $request)
    {

        list($form, $data) = $this->paymentReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $data['vendor'] = $request->query->get('vendor');
        $payments = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->paymentReport($data));

        return $this->render('PmsCoreBundle:Report:paymentReport.html.twig', array(
            'payments' => $payments,
            'form' => $form->createView(),
        ));
    }

    public function paymentReportExcelAction(Request $request)
    {

        list($form, $data) = $this->PaymentReportSearchForm($request);
        $data['vendor'] = $request->request->get('vendor');
        $payments = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->paymentReport($data);
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Request Date',
            'C9'=>'Payment Date',
            'D9'=>'PO Date',
            'E9'=>'Vendor',
            'F9'=>'PO No',
            'G9'=>'Total PO Amount',
            'H9'=>'Payment Amount',
            'I9'=>'Bank Name',
            'J9'=>'Cheque No',
//            'K9'=>'Bill No',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PaymentReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("H5", 'Payment Report');
        if(!empty($payments)){

            $index = 11;
            $count = 1;
            foreach($payments as $payment){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $payment['requestDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $payment['paymentDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $payment['createdDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $payment['vendorName'] ? $payment['vendorName']:'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $payment['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $payment['netTotal']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $payment['paymentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $payment['name'] ? $payment['name']:'N/A');
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $payment['cheque']);
//                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $payment['billNumber'] ? $payment['billNumber']:'N/A');

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
    public function paginate($dql)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (is_string($dql)) {
            $query = $em->createQuery($dql);
        } else {
            $query = $dql;
        }

        $paginator = $this->get('knp_paginator');
        $value = $paginator->paginate(
            $query,
            $page = $this->get('request')->query->get('page', 1) /*page number*/,
            50/*limit per page*/
        );

        return $value;
    }
}