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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class ItemReportController extends Controller
{
    public function itemReportSearchForm($request)
    {
        $form = new ItemReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function itemReportAction(Request $request)
    {
        list($form, $data) = $this->itemReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $itemUses = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->itemReport($data));
        $itemTopUses = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->itemTopReport($data);

        return $this->render('PmsReportBundle:Report:itemReport.html.twig', array(
            'itemUses' => $itemUses,
            'itemTopUses' => $itemTopUses,
            'itemTopUsesForPieChart' => $itemTopUses,
            'form' => $form->createView(),
        ));
    }

    public function itemAutoCompleteAction(Request $request){
        $itemQuery = array();
        $itemName = $request->query->get('q', null);
        $item = $request->query->get('item',  null);
        if ($itemName) {
            $itemQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')
                ->itemAutoComplete($itemName, $this->getUser());

        } else if ($item) {
            $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($item);

            $itemText = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')
                ->itemAutoComplete($item->getItemName(), $this->getUser());

            $itemQuery = array('id' => $itemText[0]['id'], 'text' => $itemText[0]['text']);

        }

        return new JsonResponse($itemQuery);
    }

    public function itemReportExcelAction(Request $request)
    {
        list($form, $data) = $this->itemReportSearchForm($request);

        $itemUses = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->itemReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item',
            'C9'=>'Frequency',
            'D9'=>'Project',
            'E9'=>'Total',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ItemReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Item Report');
        if(!empty($itemUses)){
            $index = 11;
            $count = 1;
            foreach($itemUses as $itemUse){

                $item = $itemUse['itemName'] ? $itemUse['itemName'] : "";
                $totalUsed = $itemUse['totalUsed'] ? $itemUse['totalUsed'] : "";
                $project = $itemUse['projectName'] ? $itemUse['projectName'] : "";
                $total= $itemUse['total'] ? $itemUse['total'] : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $item);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $totalUsed);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $total);

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