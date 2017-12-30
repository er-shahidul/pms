<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\ReceiveItemReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Report controller.
 *
 */
class ReceiveItemReportController extends Controller
{
    public function receiveItemReportSearchForm($request)
    {
        $form = new ReceiveItemReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function receiveItemReportAction(Request $request)
    {

        list($form, $data) = $this->receiveItemReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $receiveItemUses = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')->receiveItemReport($data);
        // var_dump($receiveItemUses);die;
        $totalPoApprovedItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getApprovedPurchaseOrder($data);

        $totalNoOfPrQty = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getTotalPurchaseRequisitionItemQuantity($data);

        $totalPoReceivedItems = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')->getTotalReceiveItem($data);

        $totalCloseReceiveItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getTotalCloseReceiveItem($data);

//        var_dump($totalPoApprovedItems);
//        var_dump($totalPoReceivedItems);
//        var_dump($totalCloseReceiveItems);die;

// var_dump($totalPoApprovedItems);die;
     //   $totalRemain = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getRemainingItemQty($data);
       // var_dump($totalPoApprovedItems,$totalPoReceivedItems);die;
//        $totalNoOfIndividualCancelQty = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getTotalCancelQty($data);
//        $totalNoOfCancelQty = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getTotalCancelQty($data);


        return $this->render('PmsReportBundle:Report:receiveItemReport.html.twig', array(
            'receiveItemUses' => $receiveItemUses,
            'totalPoApprovedItems' =>$totalPoApprovedItems,
            'totalPoReceivedItems' =>$totalPoReceivedItems,
            'totalCloseReceiveItems' =>$totalCloseReceiveItems,
            'totalNoOfPrQty' =>$totalNoOfPrQty,
            'form' => $form->createView(),
        ));
    }

    public function receiveItemReportExcelAction(Request $request)
    {

        list($form, $data) = $this->receiveItemReportSearchForm($request);

        $receiveItemUses = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')->receiveItemReport($data);
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item Name',
            'C9'=>'PO No',
            'D9'=>'PO Date',
            'E9'=>'PO Qty',
            'F9'=>'Receive Qty',
            'G9'=>'Project Name',
            'H9'=>'Vendor Name',
            'I9'=>'Grn No',
            'J9'=>'Grn Date',
            'K9'=>'Grn By',
            'L9'=>'Remarks',
            'M9'=>'CloseQty',
            'N9'=>'CloseRemarks',
            'O9'=>'ClosedBy',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ReceiveItemReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("E5", 'Receive Item Report');
        if(!empty($receiveItemUses)){
            $index = 11;
            $count = 1;
            foreach($receiveItemUses as $itemUse){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $itemUse['itemName']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $itemUse['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $itemUse['poCreatedDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $itemUse['poiQuantity']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $itemUse['riQuantity']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $itemUse['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $itemUse['vendorName']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $itemUse['GrnNo']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $itemUse['receivedDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $itemUse['GrnBy']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $itemUse['Remarks']);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $itemUse['closeQty'] ? $itemUse['closeQty'] :'');
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $itemUse['closeRemark'] ? $itemUse['closeRemark']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $itemUse['closedBy'] ? $itemUse['closedBy']:'');

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
    public function purchaseOrderApproveListAction(){

        $purchaseOrderRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder');

        $purchaseOrders = $this->paginate($purchaseOrderRepository->getPurchaseOrderApprovedList());

        return $this->render('PmsReportBundle:Report:purchaseOrderApproveList.html.twig', array(
            'purchaseOrdersApprovedList' => $purchaseOrders,
        ));
    }
    public function receiveItemListAction(){

        $receiveRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem');

        $receiveItems = $this->paginate($receiveRepository->getReceiveItemList());

        return $this->render('PmsReportBundle:Report:receiveItemList.html.twig', array(
            'receiveItems' => $receiveItems,
        ));
    }
    public function notReceiveItemListAction(Request $request) {

        $url = $request->server->get('HTTP_REFERER');
        $parts = parse_url($url);

       if(isset($parts['query'])){
           parse_str($parts['query'], $query);
       } else{
           $query['search'] = null;
       }



        $receiveItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->notReceiveItemList($this->getUser(),$query['search']);
//  var_dump($receiveItems);die;
        return $this->render('PmsReportBundle:Report:receiveItemList.html.twig', array(
            'pri' => $receiveItems,
        ));


    }
} 