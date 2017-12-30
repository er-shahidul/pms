<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\TrendReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class TrendReportController extends Controller
{
    public function trendReportSearchForm($request)
    {
        $form = new TrendReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function trendReportAction(Request $request)
    {
        list($form, $data) = $this->trendReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($trendRequisitionReport, $month) = $this->getDoctrine()
                                                     ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
                                                     ->trendRequisitionReport($data);

        $trendPurchaseMonthReport = $this->getDoctrine()
                                                     ->getRepository('PmsCoreBundle:PurchaseOrderItem')
                                                     ->trendRequisitionReport($data);
        /*$trendUsageMonthReport = $this->getDoctrine()
                                                     ->getRepository('PmsCoreBundle:ReceivedItem')
                                                     ->trendUsageReport($data);*/
        $trendIssuingQuantityReport = $this->getDoctrine()
                                                     ->getRepository('PmsInventoryBundle:Delivery')
                                                     ->getTrendIssuingReport($data);
//        var_dump($trendUsageMonthReport);die;
        return $this->render('PmsReportBundle:Report:trendReport.html.twig', array(
            'form' => $form->createView(),
            'trendRequisitionReport' => $trendRequisitionReport,
            'trendPurchaseMonthReport' => $trendPurchaseMonthReport,
            'trendUsageMonthReport' => $trendIssuingQuantityReport,
            'month' => $month,
        ));
    }

    public function trendReportExcelAction(Request $request)
    {
        list($form, $data) = $this->trendReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($trendRequisitionReport, $month) = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
            ->trendRequisitionReport($data);
        $trendPurchaseMonthReport = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->trendRequisitionReport($data);

        /*$trendUsageMonthReport = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:ReceivedItem')
            ->trendUsageReport($data);*/

        $trendUsageMonthReport = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:Delivery')
            ->getTrendIssuingReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'Month',
            'B9'=>'Requisition Quantity',
            'C9'=>'Purchase Quantity',
            'D9'=>'Usage Quantity',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ItemTrendReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C4", 'Item Trend Report');

        $objPHPExcel->getActiveSheet()->setCellValue("A7", 'Item : '.$trendRequisitionReport[0]['itemName']);
        $objPHPExcel->getActiveSheet()->setCellValue("B7", 'Unit : '.$trendRequisitionReport[0]['projectName']);
        $objPHPExcel->getActiveSheet()->setCellValue("C7", 'Project : '.$trendRequisitionReport[0]['itemUnit']);
        $objPHPExcel->getActiveSheet()->setCellValue("D7", 'Year : '.$data['year']);

        if(!empty($trendRequisitionReport)){
            $index = 11;
            $count = 0;

            foreach($trendRequisitionReport as $itemUse){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $month[$count]);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $trendRequisitionReport[$count]['itemQuantity']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $trendPurchaseMonthReport[$count]['itemPoQuantity']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $trendUsageMonthReport[$count]['itemUsageQuantity']);

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