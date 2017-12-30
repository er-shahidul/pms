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
class RequisitionVsOrderSavingReportController extends Controller
{
    public function requisitionVsOrderSavingReportSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function requisitionVsOrderSavingReportAction(Request $request)
    {
        list($form, $data) = $this->requisitionVsOrderSavingReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $prVsPoSavings = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getRequisitionVsOrderSaving($data);

        return $this->render('PmsReportBundle:Report:requisitionVsOrderSavingReport.html.twig', array(
            'budgetDailys' => $prVsPoSavings,
            'form' => $form->createView(),
        ));
    }

    public function requisitionVsOrderSavingReportExcelAction(Request $request)
    {
        list($form, $data) = $this->requisitionVsOrderSavingReportSearchForm($request);

        $prVsPoSavings = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->getRequisitionVsOrderSaving($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'PR No',
            'C9'=>'Project Name',
            'D9'=>'Item Name',
            'E9'=>'PR Qty',
            'F9'=>'Amount',
            'G9'=>'PO',
            'J9'=>'Saving'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PrPoSavings'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("F5", 'Requisition Order Savings');
        if(!empty($prVsPoSavings)){
            $index = 10;
            $count = 1;
            foreach($prVsPoSavings as $budgetDaily){

                $prNumber = $budgetDaily->getPurchaseRequisition()->getId() ? $budgetDaily->getPurchaseRequisition()->getId() : "";
                $projectName = $budgetDaily->getPurchaseRequisition()->getProject()->getProjectName() ? $budgetDaily->getPurchaseRequisition()->getProject()->getProjectName() : "";
                $itemName = $budgetDaily->getItem()->getItemName() ? $budgetDaily->getItem()->getItemName() : "";
                $qty = $budgetDaily->getQuantity() ? $budgetDaily->getQuantity() : "";
                $totalPrice = $budgetDaily->getTotalPrice() ? $budgetDaily->getTotalPrice() : 0;

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $prNumber);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $qty);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $totalPrice);

                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, 'PO No');
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, 'PO Qty');
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, 'PO Amount');

                $total = 0;
                foreach($budgetDaily->getpurchaseOrderItems() as $budgetDailyPO){
                    $index = $index + 1;

                    $numberPO = $budgetDailyPO->getPurchaseOrder()->getId() ? $budgetDailyPO->getPurchaseOrder()->getId() : "";
                    $qtyPO = $budgetDailyPO->getQuantity() ? $budgetDailyPO->getQuantity() : "";
                    $amountPO = $budgetDailyPO->getAmount() ? $budgetDailyPO->getAmount() : 0;

                    $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $numberPO);
                    $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $qtyPO);
                    $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $amountPO);

                    $index++;
                    $total = $amountPO + $total;
                }

                $prPoSave = $total - $totalPrice;
                $objPHPExcel->getActiveSheet()->setCellValue("J".($index-1), $prPoSave);

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
            10/*limit per page*/
        );

        return $value;
    }
}