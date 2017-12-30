<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\ItemReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class ItemOverViewReportController extends Controller
{
    public function itemOverViewReportSearchForm($request)
    {
        $form = new ItemReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function overViewReportAction(Request $request)
    {
        list($form, $data) = $this->itemOverViewReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $itemUses = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->overViewReport($data);

//        var_dump($itemUses);die;
        $itemUses = $this->paginate($itemUses);

        return $this->render('PmsReportBundle:Report:itemOverViewReport.html.twig', array(
            'itemUses' => $itemUses,
            'form' => $form->createView(),
        ));
    }

    public function overViewReportExcelAction(Request $request)
    {
        list($form, $data) = $this->itemOverViewReportSearchForm($request);

        $itemUses = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->overViewReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item',
            'C9'=>'Quantity',
            'D9'=>'Total',

            'E9'=>'Project Name',
            'F9'=>'Highest Date',
            'G9'=>'Highest PO',
            'H9'=>'Highest Price',
            'I9'=>'Lowest Date',
            'J9'=>'Lowest PO',
            'K9'=>'Lowest Price',
            'L9'=>'Last Date',
            'M9'=>'Last PO',
            'N9'=>'Last Price'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ItemOverView'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("F5", 'Item Over View');
        if(!empty($itemUses)){
            $index = 11;
            $count = 1;
            foreach($itemUses as $itemUse){

                $itemName = $itemUse['itemName'] ? $itemUse['itemName'] : "";
                $quantity = $itemUse['quantity'] ? $itemUse['quantity'] : "0";
                $total = $itemUse['total'] ? $itemUse['total'] : "0";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $quantity);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $total);

                $i = 0;
                foreach($itemUse['projectSummary'] as $projectSummary){
                    if($data['project']){
                        if($data['project'] == $projectSummary['projectId']) {
                            $i = $i + 1;

                            $projectName = $projectSummary['projects_name'] ? $projectSummary['projects_name'] : "";
                            $highestDate = $projectSummary['max_price_date'] ? $projectSummary['max_price_date'] : "";
                            $highestPo = $projectSummary['max_poId'] ? $projectSummary['max_poId'] : "";
                            $highestPrice = $projectSummary['projectHighest'] ? $projectSummary['projectHighest'] : "";
                            $lowestDate = $projectSummary['min_price_date'] ? $projectSummary['min_price_date'] : "";
                            $lowestPo = $projectSummary['min_poId'] ? $projectSummary['min_poId'] : "";
                            $lowestPrice = $projectSummary['projectLowest'] ? $projectSummary['projectLowest'] : "";

                            $lastDate = $projectSummary['dateMax'] ? $projectSummary['dateMax'] : "";
                            $lastDate = strtotime($lastDate);
                            $lastDate = date('Y-m-d',$lastDate);

                            $lastPo = $projectSummary['last_poId'] ? $projectSummary['last_poId'] : "";
                            $lastPrice = $projectSummary['last_price'] ? $projectSummary['last_price'] : "";

                            $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $projectName);
                            $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $highestDate);
                            $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $highestPo);
                            $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $highestPrice);
                            $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $lowestDate);
                            $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $lowestPo);
                            $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $lowestPrice);
                            $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $lastDate);
                            $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $lastPo);
                            $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $lastPrice);

                            $index++;
                        }
                    }else{
                        $i = $i + 1;

                        $projectName = $projectSummary['projects_name'] ? $projectSummary['projects_name'] : "";
                        $highestDate = $projectSummary['max_price_date'] ? $projectSummary['max_price_date'] : "";
                        $highestPo = $projectSummary['max_poId'] ? $projectSummary['max_poId'] : "";
                        $highestPrice = $projectSummary['projectHighest'] ? $projectSummary['projectHighest'] : "";
                        $lowestDate = $projectSummary['min_price_date'] ? $projectSummary['min_price_date'] : "";
                        $lowestPo = $projectSummary['min_poId'] ? $projectSummary['min_poId'] : "";
                        $lowestPrice = $projectSummary['projectLowest'] ? $projectSummary['projectLowest'] : "";

                        $lastDate = $projectSummary['dateMax'] ? $projectSummary['dateMax'] : "";
                        $lastDate = strtotime($lastDate);
                        $lastDate = date('Y-m-d',$lastDate);

                        $lastPo = $projectSummary['last_poId'] ? $projectSummary['last_poId'] : "";
                        $lastPrice = $projectSummary['last_price'] ? $projectSummary['last_price'] : "";

                        $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $projectName);
                        $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $highestDate);
                        $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $highestPo);
                        $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $highestPrice);
                        $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $lowestDate);
                        $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $lowestPo);
                        $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $lowestPrice);
                        $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $lastDate);
                        $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $lastPo);
                        $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $lastPrice);

                        $index++;
                    }
                }

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

    public function overViewReportItemDetailsAction(Request $request)
    {
        $data = $request->query->all();

        $itemDetailsFromItemOverView = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->itemDetailsFromProjectSpendByPurchaseTypeReport($data);
        $itemName = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($data['item']);

        return $this->render('PmsReportBundle:Report:itemDetailsFromItemOverView.html.twig', array(
            'itemDetailsFromItemOverView' => $itemDetailsFromItemOverView,
            'itemName' => $itemName,
        ));
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
            5/*limit per page*/
        );

        return $value;
    }
}