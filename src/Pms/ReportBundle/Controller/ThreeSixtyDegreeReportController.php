<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\ThreeSixtyDegreeSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class ThreeSixtyDegreeReportController extends Controller
{

    public function threeSixtyDegreeReportAction(Request $request)
    {
        $form = $this->getSearchQueryFOrm($request);

        return $this->render('PmsReportBundle:Report:threeSixtyDegree.html.twig', array(
            'form' => $form->createView(),
            'lists' => $this->dataLists($form->getData()),
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

    public function threeSixtyDegreeReportExcelAction(Request $request)
    {

        $form = new ThreeSixtyDegreeSearchType();
        $data = $request->get($form->getName());
        $lists = $this->dataLists($data);
        // Export LRP wise data
    $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Project',
            'C9'=>'Area',
            'D9'=>'Item Name',
            'E9'=>'Unit',
            'F9'=>'Total PR Qty',
            'G9'=>'Total PR Cancel Qty',
            'H9'=>'Avg Rate',
            'I9'=>'Item Amount',
            'J9'=>'PO Qty',
            'K9'=>'PO Closed Qty',
            'L9'=>'Grn Qty',
            'M9'=>'Issue Qty',


        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'VendorPerformanceOverview'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Three Sixty Degree Report');
        if(!empty($lists)){
            $index = 10;
            $count = 1;
            foreach($lists as $list){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $list['project']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list['areaName']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list['item']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list['unit']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list['prQty']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list['PrCancelQty']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $list['itemPrice']?$list['itemPrice']/$list['PoQty']:0 );
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $list['itemPrice']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $list['PoQty']);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $list['PoCloseQty']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $list['grnQty']);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $list['issueQty']);
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
            25/*limit per page*/
        );

        return $value;
    }

    /**
     * @param $data
     * @return array
     */
    private function dataLists($data)
    {
        if(!array_filter($data)){
            return;
        }

        $prIndividualItemCancelQty = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
            ->totalRequisitionIndividualItemQtyCanceled($data);

        $totalIssuingQty = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:DailyInventory')
            ->totalIssueQty($data);

        $prInfo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->getPRReportInfo($data);

        $poInfo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrder')
            ->getPoReportInFo($data);

        $poCloseInfo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->poCloseQty($data);
        $grnInfo     = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->getGrnQty($data);


        $lists = array();
        foreach ($prInfo as $id => $totalQty) {

            $itemDetails = array(
                'project'     => $totalQty['projectName'],
                'areaName'    => $totalQty['areaName'],
                'item'        => $totalQty['itemName'],
                'unit'        => $totalQty['itemUnit'],
                'prQty'       => $totalQty['totalPrQty'],
                'itemPrice'   => isset($poInfo[$id])?$poInfo[$id]['poAmount']:0,
                'PrCancelQty' => isset($prIndividualItemCancelQty[$id]) ? $prIndividualItemCancelQty[$id]['cancelQty'] : 0,
                'PoQty'       => isset($poInfo[$id]) ? $poInfo[$id]['PoQty'] : 0,
                'PoCloseQty'  => isset($poCloseInfo[$id]) ? $poCloseInfo[$id]['closeQty'] : 0,
                'grnQty'      => isset($grnInfo[$id]) ? $grnInfo[$id]['GrnQty'] : 0,
                'issueQty'    => isset($totalIssuingQty[$id]) ? $totalIssuingQty[$id]['issueQty'] : 0,
            );

            $lists[] = $itemDetails;

        }

        return $lists;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\Form\Form
     */
    private function getSearchQueryFOrm(Request $request)
    {
        $formType = new ThreeSixtyDegreeSearchType();

        $form = $this->createForm($formType);
        $form->submit($request->query->get($formType->getName()));

        return $form;
    }
}