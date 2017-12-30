<?php

namespace Pms\InventoryBundle\Controller;


use DateTime;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\CoreBundle\Controller\BaseController;

use Pms\InventoryBundle\Entity\TotalReceiveItem;
use Pms\InventoryBundle\Form\DeliveryReportSearchFormType;
use Pms\InventoryBundle\Form\InventoryItemWiseReportSearchType;
use Pms\InventoryBundle\Form\InventoryStockReportSearchType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;


class ReportController extends BaseController
{

    public function indexAction(Request $request)
    {

        list($form, $data) = $this->inventoryStockReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $item       = $request->query->all();
        if(!empty($data)){

        $data['item'] = $item['item'];
        }

        $stockLists = $this->inventoryStockReport($data);
        $totalStock = 0;
        foreach($stockLists as $totalStockAmount){

            $totalStock +=( $totalStockAmount['unitPrice'] * $totalStockAmount['closingItem'] );
        }
        $stockList = $this->paginate($stockLists);

        return $this->render(
            'PmsInventoryBundle:Report:inventoryStockReport.html.twig',array(
            'stockLists'=>$stockList,
            'totalStock' =>$totalStock,
            'form' => $form->createView()
        ));

    }

    public function deliveryReportAction(Request $request) {


        list($form, $data) = $this->deliveryReportSearchForm($request);

        if(isset($_GET['item'])){
        $data['item'] = $_GET['item'];
        }
        $form = $this->createForm($form);
        $form->submit($data);

        $deliveryLists = $this->getDoctrine()->getRepository('PmsInventoryBundle:DeliveryItem')
                                             ->DeliveryReportData($data, $this->getUser());

      //   $poiLists = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getDataByProjectAndItemWise($data);
// var_dump($poiLists);die;

        return $this->render(
            'PmsInventoryBundle:Report:deliveryReport.html.twig',array(
            'deliveryLists' => $deliveryLists,
            'form' => $form->createView()
        ));
    }

    public function inventoryStockExcelReportAction(Request $request)
    {
        $item       = $request->request->all();
        list($form,$data) = $this->inventoryStockReportSearchForm($request);
        if(!empty($data)){
            $data['item'] = $item['item'];
        }

        $stockLists = $this->inventoryStockReport($data);


        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Open Date',
            'C9'=>'Receive Date',
            'D9'=>'Item',
            'E9'=>'Unit',
            'F9'=>'Opening',
            'G9'=>'Receiving',
            'H9'=>'Total Stock',
            'I9'=>'Issues',
            'J9'=>'Closing',
            'K9'=>'Unit Price',
            'L9'=>'Total Amount',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'InventoryStock'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Inventory Stock Report');

        if(!empty($stockLists)){
            $index = 11;
            $count = 1;
            foreach($stockLists as $stockList){
//var_dump($stockList);die;
                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $stockList['openDate'] ? $stockList['openDate']->format('Y-m-d'):' ');
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $stockList['receivedDate'] ? $stockList['receivedDate']->format('Y-m-d'):' ');

                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $stockList['item']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $stockList['unit']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $stockList['openItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $stockList['receivingItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $stockList['totalStock']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $stockList['issuingItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $stockList['closingItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $stockList['unitPrice']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $stockList['closingItem'] * $stockList['unitPrice']);

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

    public function deliveryExcelReportAction(Request $request)
    {
        list($form, $data) = $this->deliveryReportSearchForm($request);

        if($request->request->get('item')){
            $data['item'] = $request->request->get('item');
        }

        $deliveryLists = $this->getDoctrine()->getRepository('PmsInventoryBundle:DeliveryItem')
            ->DeliveryReportData($data, $this->getUser());


        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item Name',
            'C9'=>'Unit',
            'D9'=>'Avg Price',
            'E9'=>'Issued Date',
            'F9'=>'Issued Quantity',
            'G9'=>'Issued By',
            'H9'=>'Issued To',
            'I9'=>'Project From',
            'J9'=>'Project To',
            'K9'=>'Gate Pass No',
            'L9'=>'Category Name',
            'M9'=>'Sub Category Name',
            'N9'=>'Cost Header',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'delivery'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Issue Report');


        if(!empty($deliveryLists)){
            $index = 11;
            $count = 1;
            foreach($deliveryLists as $deliveryList){

                $customer = $deliveryList->getDelivery()->getCustomerId() ?
                    ($deliveryList->getDelivery()->getCustomerId() ? $deliveryList->getDelivery()->getCustomerId()->getUserName():''):
                    ($deliveryList->getDelivery()->getIssuedToCustomer() ? $deliveryList->getDelivery()->getIssuedToCustomer()->getUsername():'');

                $issuedProject = $deliveryList->getDelivery()->getIssuedToProject() ?
                                 $deliveryList->getDelivery()->getIssuedToProject()->getProjectName() : '';
                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $deliveryList->getItem()->getItemName());
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $deliveryList->getItem()->getItemUnit());
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $deliveryList->getAvgPrice() ? $deliveryList->getAvgPrice(): '');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $deliveryList->getDelivery()->getCreatedDate()->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $deliveryList->getQuantity());
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $deliveryList->getDelivery()->getCreatedBy() ? $deliveryList->getDelivery()->getCreatedBy()->getUsername():'');
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $customer ? $customer :'');
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $deliveryList->getDelivery()->getProject()->getProjectName());
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index,  $issuedProject);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $deliveryList->getDelivery()->getGetPass());
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $deliveryList->getDelivery()->getCategory()->getCategoryName());
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $deliveryList->getDelivery()->getSubCategory()->getSubCategoryName());
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $deliveryList->getDelivery()->getCostHeader()->getTitle());
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

    public function inventoryStockReportSearchForm($request)
    {

        $result = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->getAll();
        $recieveItems = array('' => 'Select Item');
        /** @var TotalReceiveItem $item */
        foreach ($result as $item) {
            $recieveItems[$item['id']] = $item['itemName'];
        }
        $form = new InventoryStockReportSearchType($recieveItems,$this->getUser());
        $data = $request->get($form->getName());
        return array($form, $data);
    }
    public function deliveryReportSearchForm($request)
    {

        $result = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->getAll();
        $recieveItems = array('' => 'Select Item');
        /** @var TotalReceiveItem $item */
        foreach ($result as $item) {
            $recieveItems[$item['id']] = $item['itemName'];
        }
        $form = new DeliveryReportSearchFormType($recieveItems,$this->getUser());
        $data = $request->get($form->getName());
        return array($form, $data);
    }


    public function itemWiseReportAction(Request $request)
    {

        list($form, $data) = $this->inventoryItemWiseReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $stockLists = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->getInventoryItemWiseReport($data);

        return $this->render(
            'PmsInventoryBundle:Report:inventoryItemWiseReport.html.twig',array(
            'stockLists'=>$stockLists,
            'form' => $form->createView()
        ));
    }

    public function inventoryItemWiseReportSearchForm($request)
    {

        $result = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->getAll();
        $recieveItems = array('' => 'Select Item');
        /** @var TotalReceiveItem $item */
        foreach ($result as $item) {
            $recieveItems[$item['id']] = $item['itemName'];
        }

        $form = new InventoryItemWiseReportSearchType($recieveItems);
        $data = $request->get($form->getName());
        return array($form, $data);
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
    private function inventoryStockReport($data)
    {

        $totalOpeningQty = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->getTotalOpeningQty($data);

        $totalReceivingQty = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->getTotalReceivingQty($data);



        $totalIssuingQty = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:DailyInventory')
            ->getTotalIssuingQty($data);

        $stockList = array();

        foreach ($totalOpeningQty as $id => $totalQty) {

            $itemDetails = array(
                'item'           => $totalQty['itemName'],
                'unit'           => $totalQty['itemUnit'],
                'openItem'       => $totalQty['openItem'],
                'openingPrice'   => $totalQty['openItem'] * $totalQty['price'],
//                'openingTotal'   => $totalQty['openingTotal'],
                'receivingItem'  => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['quantity'] : 0,
                'receivingPrice' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['price'] : 0,
                'issuingItem'    => isset($totalIssuingQty[$id]) ? $totalIssuingQty[$id]['issuingQuantity'] : 0,
                'openDate'       => $totalQty['openingDate'],
                'receivedDate'   => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['receivedDate'] : null,
                'receiveTotal' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['receivingTotal'] : 0,


            );

            $itemDetails['closingItem']  = $itemDetails['openItem'] + $itemDetails['receivingItem'] - $itemDetails['issuingItem'];
            $itemDetails['issuingPrice'] = ($itemDetails['openingPrice'] + $itemDetails['receivingPrice']) / 2;
            $itemDetails['issuingTotal'] = $itemDetails['issuingPrice'] * $itemDetails['issuingItem'];
            $itemDetails['closingTotal'] = $itemDetails['closingItem'] * $itemDetails['issuingPrice'];
            $itemDetails['totalStock']   = $itemDetails['openItem'] + $itemDetails['receivingItem'];
            $itemDetails['receivingTotal'] = $itemDetails['receiveTotal'];
            $itemDetails['totalAmount']  = $itemDetails['openingPrice'] + $itemDetails['receivingTotal'];


            if($itemDetails['totalStock'] != 0 ) {
                $itemDetails['unitPrice'] = $itemDetails['totalAmount'] / $itemDetails['totalStock'];
            }else{
                $itemDetails['unitPrice'] = $itemDetails['openingPrice'];
            }


            $stockList[] = $itemDetails;

        }

        return $stockList;
    }

    /**
     * @param $data
     * @return array
     */
    private function searchDataSet($data)
    {
        if (!empty($data['search'])) {
            $data1 = array(
                'start_date' => $data['search']['start_date'],
                'item'       => $data['item'],
                'project'    => $data['search']['project'],
            );

            return $data1;
        }

        return $data1;
    }
    /*private function inventoryStockReport($data)
    {
        $totalOpeningQty = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->getTotalOpeningQty($data);

        $totalReceivingQty = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->getTotalReceivingQty($data);

        $totalIssuingQty = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:DailyInventory')
            ->getTotalIssuingQty($data);

        $stockList = array();
        foreach ($totalOpeningQty as $id => $totalQty) {

            $itemDetails = array(
                'item'           => $totalQty['itemName'],
                'unit'           => $totalQty['itemUnit'],
                'openItem'       => $totalQty['openItem'],
                'openingPrice'   => $totalQty['price'],
                'openingTotal'   => $totalQty['openingTotal'],
                'receivingItem'  => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['quantity'] : 0,
                'receivingPrice' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['price'] : 0,
                'receivingTotal' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['receivingTotal'] : 0,
                'issuingItem'    => isset($totalIssuingQty[$id]) ? $totalIssuingQty[$id]['issuingQuantity'] : 0

            );

            $itemDetails['closingItem']  = $itemDetails['openItem'] + $itemDetails['receivingItem'] - $itemDetails['issuingItem'];
            $itemDetails['issuingPrice'] = ($itemDetails['openingPrice'] + $itemDetails['receivingPrice']) / 2;
            $itemDetails['issuingTotal'] = $itemDetails['issuingPrice'] * $itemDetails['issuingItem'];
            $itemDetails['closingTotal'] = $itemDetails['closingItem'] * $itemDetails['issuingPrice'];

            $stockList[] = $itemDetails;

        }

        return $stockList;
    }*/

}
