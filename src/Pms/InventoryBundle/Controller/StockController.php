<?php

namespace Pms\InventoryBundle\Controller;


use PHPExcel;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\CoreBundle\Controller\BaseController;
use Pms\InventoryBundle\Entity\TotalReceiveItem;
use Pms\InventoryBundle\Form\TotalReceiveItemType;
use Pms\ReportBundle\Form\BetweenTwoDateSearchType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StockController extends BaseController
{
    public function indexAction(Request $request)

    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        $form = $this->createForm($form);
        $form->submit($data);

        list($stockLists, $projects, $totalItems) = $this->stockItemSearch($request);
//        var_dump($stockLists);die;
        $stockList = $this->paginate($stockLists);
        return $this->render(
            'PmsInventoryBundle:Stock:list.html.twig',
            array('stockLists' => $stockList,
                  'totalItems'    => $totalItems,
                  'projects'    => $projects,
                  'form' => $form->createView()
                 )
        );
    }



    public function saveMinStockAction(Request $request)
    {
        $minStockArray       = $request->request->all();
        $totalReceiveItemObj = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->find($minStockArray['pk']);

        $totalReceiveItemObj->setMinStockCount($minStockArray['value']);
        $this->getDoctrine()->getManager()->persist($totalReceiveItemObj);
        $this->getDoctrine()->getManager()->flush();

        return new Response();
    }
    public function saveMaxStockAction(Request $request)
    {
        $maxStockArray       = $request->request->all();
        $totalReceiveItemObj = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->find($maxStockArray['pk']);

        $totalReceiveItemObj->setMaxStockCount($maxStockArray['value']);
        $this->getDoctrine()->getManager()->persist($totalReceiveItemObj);
        $this->getDoctrine()->getManager()->flush();

        return new Response();
    }

    public function stockItemAddAction(Request $request) {


        $totalReceiveItem = new TotalReceiveItem();

        $form = $this->createForm(new TotalReceiveItemType($this->getUser()), $totalReceiveItem);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $item = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->findOneBy(
                    array('item' =>$totalReceiveItem->getItem() ,'project' =>$totalReceiveItem->getProject() )
                );
              if($item){
                  $massage = 'Project & Item already stored';
                  $this->get('session')->getFlashBag()->add('error', $massage);
                  return $this->redirect($this->generateUrl('stock_list'));
              }
                $totalReceiveItem->setCreatedDate(new \DateTime());
                $totalReceiveItem->setItem($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($totalReceiveItem->getItem()));
                $this->getDoctrine()->getRepository("PmsInventoryBundle:TotalReceiveItem")->create($totalReceiveItem);
                $massage = 'Stock Successfully Inserted';
                $this->successMessage($massage);

                return $this->redirect($this->generateUrl('stock_list'));

            }
        }

        return $this->render('PmsInventoryBundle:Stock:addForm.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function stockItemUpdateAction(Request $request, TotalReceiveItem $entity) {


        $form = $this->createForm(new TotalReceiveItemType($this->getUser()),$entity);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
               // $entity->setItem($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($entity->getItem()));
                $this->getDoctrine()->getRepository("PmsInventoryBundle:TotalReceiveItem")->update($entity);
                $massage = 'Stock Item Successfully Updated';
                $this->successMessage($massage);
                return $this->redirect($this->generateUrl('stock_list'));

            }
        }
        return $this->render('PmsInventoryBundle:Stock:addForm.html.twig', array(
            'entity' =>$entity,
            'form' => $form->createView()
        ));
    }

    public function existItemStockCheckAction(Request $request){

        $data = $request->request->all();

        $item = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->findOneBy(
            array('item' => $data['itemName'],'project' =>$data['projectName'] )
        );

        return $this->checkStockInHandByProjectAndItem($item);

    }

    public function getItemPriceAction()
    {

    }
    public function saveOpenItemAction(Request $request)
    {
        $openItemArray       = $request->request->all();
        $totalReceiveItemObj = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->find($openItemArray['pk']);
        $totalReceiveItemObj->setOpenItem($openItemArray['value']);
        $totalReceiveItemObj->setOpeningDate(new \DateTime());

        $this->getDoctrine()->getManager()->persist($totalReceiveItemObj);
        $this->getDoctrine()->getManager()->flush();

        return new Response();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function stockItemSearch(Request $request)
    {
        $data       = $request->query->all();
        if(!empty($data['search'])){
            $data1 = array(
                'start_date'=>$data['search']['start_date'],
                'item'=>$data['item'],
                'project'=>$data['project'],
            );
        }

      if(!empty($data1['start_date']) && !empty($data1['item']) || !empty($data1['project'])){
        $stockLists = $this->inventoryStockReport($data1);
      }else{
          $stockLists = array();
      }
        // $projects   = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->findBy(array('status' => 1),array('projectName'=>'ASC'));
        $projects = $this->getDoctrine()
                         ->getRepository('PmsSettingBundle:Project')
                          ->getProjectForUser($this->getUser());
        $totalItems = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')->getAll();


        return array($stockLists, $projects, $totalItems);
    }
    /**
     * @param Request $request
     * @return array
     */
    public function stockItemSearchExcel(Request $request)
    {
        $data       = $request->request->all();

        if(!empty($data['search'])){
            $data1 = array(
                'start_date'=>$data['search']['start_date'],
                'item'=>$data['item'],
                'project'=>$data['project'],
            );
        }

        if(!empty($data1['start_date']) && !empty($data1['item']) || !empty($data1['project'])){
            $stockLists = $this->inventoryStockReport($data1);
        }else{
            $stockLists = array();
        }
        return $stockLists;
    }

    /**
     * @param $item
     * @return Response
     */
    public function checkStockInHandByProjectAndItem($item)
    {
        if ($item) {
            $return = array("responseCode" => 200, "itemAndProject" => "project & Item already stored");
            $return = json_encode($return);

            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            return new Response();
        }
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
/*
        $receiveByProject = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:TotalReceiveItem')
            ->getTotalReceiveByProjectQty($data);*/


        $totalReceivingQty = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->getTotalReceivingQty($data);

        $totalIssuingQty = $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:DailyInventory')
            ->getTotalIssuingQty($data);


        $stockList = array();
        foreach ($totalOpeningQty as $id => $totalQty) {

            $itemDetails = array(
                'id'             =>$totalQty['id'],
                'item'           => $totalQty['itemName'],
                'unit'           => $totalQty['itemUnit'],
                'openItem'       => $totalQty['openItem'],
                'openingPrice'   => $totalQty['price'],
                'projectTo'      => $this->getItemDeliverToProject($data['project'],$totalQty['itemId']),
                'maxStockCount'  => $totalQty['maxStockCount'],
                'minStockCount'  => $totalQty['minStockCount'],
                'openingTotal'   => $totalQty['openingTotal'],
                'receivingItem'  => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['quantity'] : 0,
                'receivingPrice' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['price'] : 0,
                'receivingTotal' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['receivingTotal'] : 0,
                'issuingItem'    => isset($totalIssuingQty[$id]) ? $totalIssuingQty[$id]['issuingQuantity'] : 0

            );

            $itemDetails['closingItem']  = $itemDetails['openItem'] + $itemDetails['projectTo'] + $itemDetails['receivingItem'] - $itemDetails['issuingItem'];
            $itemDetails['issuingPrice'] = ($itemDetails['openingPrice'] + $itemDetails['receivingPrice']) / 2;
            $itemDetails['issuingTotal'] = $itemDetails['issuingPrice'] * $itemDetails['issuingItem'];
            $itemDetails['closingTotal'] = $itemDetails['closingItem'] * $itemDetails['issuingPrice'];

            $stockList[] = $itemDetails;

        }

    /*    $stockList1 = array();
        foreach ($receiveByProject as $id => $totalQty) {

            $itemDetails = array(
                'id'             =>$totalQty['id'],
                'item'           => $totalQty['itemName'],
                'unit'           => $totalQty['itemUnit'],
                'openItem'       => $totalQty['openItem'],
                'openingPrice'   => $totalQty['price'],
                'projectTo'      => $totalQty['receiveByProjectQty'],
                'maxStockCount'  => $totalQty['maxStockCount'],
                'minStockCount'  => $totalQty['minStockCount'],
                'openingTotal'   => $totalQty['openingTotal'],
                'receivingItem'  => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['quantity'] : 0,
                'receivingPrice' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['price'] : 0,
                'receivingTotal' => isset($totalReceivingQty[$id]) ? $totalReceivingQty[$id]['receivingTotal'] : 0,
                'issuingItem'    => isset($totalIssuingQty[$id]) ? $totalIssuingQty[$id]['issuingQuantity'] : 0

            );

            $itemDetails['closingItem']  = $itemDetails['openItem'] + $itemDetails['projectTo'] + $itemDetails['receivingItem'] - $itemDetails['issuingItem'];
            $itemDetails['issuingPrice'] = ($itemDetails['openingPrice'] + $itemDetails['receivingPrice']) / 2;
            $itemDetails['issuingTotal'] = $itemDetails['issuingPrice'] * $itemDetails['issuingItem'];
            $itemDetails['closingTotal'] = $itemDetails['closingItem'] * $itemDetails['issuingPrice'];

            $stockList1[] = $itemDetails;

        }*/
       // $result = array_merge($stockList, $stockList1);

//        return $result;
        return $stockList;
    }

    public function getItemDeliverToProject($data,$item){
        $receiveByProjectQty =  $this->getDoctrine()
            ->getRepository('PmsInventoryBundle:Delivery')
            ->getTotalReceiveByProjectQty($data,$item);

        return $receiveByProjectQty['itemUsageQuantity'];
    }



    public function stockItemExcelAction(Request $request)
    {

        $stockLists = $this->stockItemSearchExcel($request);

     //   $stockLists = $this->stockItemSearch($request);

// var_dump($stockLists[0]->getProject()->getProjectName());die;
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item',
            'C9'=>'Unit',
            'D9'=>'Opening',
            'E9'=>'Received By PO',
            'F9'=>'Received By Project',
            'G9'=>'Delivered Qty',
            'H9'=>'Stock In Hand',
            'I9'=>'Min Stock',
            'J9'=>'Max Stock',
        );



        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'StockItem'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Stock Item Excel');
        if(!empty($stockLists)){
            $index = 11;
            $count = 1;
            foreach($stockLists as $stockList){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $stockList['item']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $stockList['unit']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $stockList['openItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $stockList['receivingItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $stockList['projectTo'] ? $stockList['projectTo']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $stockList['issuingItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $stockList['closingItem']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $stockList['maxStockCount']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $stockList['minStockCount']);
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
