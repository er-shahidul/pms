<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;

use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\VendorOverviewSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class VendorOverviewReportController extends Controller
{
    public function vendorOverviewReportSearchForm($request)
    {
        $form = new VendorOverviewSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function vendorOverviewReportAction(Request $request)
    {
        list($form, $data) = $this->vendorOverviewReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $startOfTheMonth = date($data['month']);
        $endOfTheMonth = date("Y-m-t", strtotime($startOfTheMonth));
        $data['start_month'] = $startOfTheMonth;
        $data['end_month'] = $endOfTheMonth;


        $topTenVendorOverView = $this->getDoctrine()
                            ->getRepository('PmsCoreBundle:PurchaseOrder')
                            ->getTopTenVendorOverView($data);

        return $this->render('PmsReportBundle:Report:vendorOverviewReport.html.twig', array(
            'topTenVendorOverViews' => $topTenVendorOverView,
            'form' => $form->createView(),
        ));
    }


    public function formatMoney($number, $fractional = false)
    {
        if ($fractional) {
            $number = sprintf('%.2f', $number);
        }
        while (true) {
            $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
            if ($replaced != $number) {
                $number = $replaced;
            } else {
                break;
            }
        }
        return $number;
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
            25/*limit per page*/
        );

        return $value;
    }

    public function vendorOverViewExcelAction(Request $request)
    {
        list($form, $data) = $this->vendorOverviewReportSearchForm($request);

        $startOfTheMonth = date($data['month']);
        $endOfTheMonth = date("Y-m-t", strtotime($startOfTheMonth));
        $data['start_month'] = $startOfTheMonth;
        $data['end_month'] = $endOfTheMonth;

//var_dump($data);die;
        $topTenVendorOverViews = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrder')
            ->getTopTenVendorOverView($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'VendorName',
            'C9'=>'Payment Type',
            'D9'=>'PO Amount',
            'E9'=>'Area',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'vendorOverView'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Vendor Overview Report');
        if(!empty($topTenVendorOverViews)){
            $index = 11;
            $count = 1;
            foreach($topTenVendorOverViews as $topTenVendorOverView){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $topTenVendorOverView['vendorName']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $topTenVendorOverView['paymentType']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $topTenVendorOverView?$topTenVendorOverView['PoAmount']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $topTenVendorOverView?$topTenVendorOverView['areaName']:'');

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