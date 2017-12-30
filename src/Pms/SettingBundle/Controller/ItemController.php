<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Form\ItemType;

use Pms\SettingBundle\Form\SearchForm\ItemSearchType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Item controller.
 *
 */
class ItemController extends Controller
{
    public function itemSearchForm($request)
    {
        $form = new ItemSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function indexAction(Request $request, $status = 'active')
    {
        list($form, $data) = $this->itemSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        switch (true) {
            case isset($data['item']) && !empty($data['item']) && isset($data['itemType']) && !empty($data['itemType']):
                $items = $this->paginate($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch($data['item'], $data['itemType'], $status));
                break;
            case isset($data['item']) && !empty($data['item']):
                $items = $this->paginate($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch($data['item'], 0, $status));
                break;
            case isset($data['itemType']) && !empty($data['itemType']):
                $items = $this->paginate($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch(0, $data['itemType'], $status));
                break;
            default:
                $items = $this->paginate($this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch(0, 0, $status));
        }

        return $this->render('PmsSettingBundle:Item:home.html.twig', array(
            'items' => $items,
            'status' => $status,
            'url' => $request->server->get('REQUEST_URI'),
            'form' => $form->createView(),
        ));
    }




    public function addAction(Request $request)
    {
        $item = new Item();

        $form = $this->createForm(new ItemType(), $item);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $item->setCreatedBy($this->getUser());
                $item->setCreatedDate(new \DateTime());
                $item->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->create($item);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Item Add Successfully'
                );

                return $this->redirect($this->generateUrl('item'));
            }
        }

        return $this->render('PmsSettingBundle:Item:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function itemsListExcelAction(Request $request, $status = 'active')
    {
        list($form, $data) = $this->itemSearchForm($request);

        switch (true) {
            case isset($data['item']) && !empty($data['item']):
                $items = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch($data['item'], 0, $status);
                break;
            case isset($data['itemType'])&& !empty($data['itemType']):
                $items = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch(0, $data['itemType'], $status);
                break;
            default:
                $items = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->getItemSearch(0, 0, $status);
        }

        $items = $items->getQuery()->getResult();

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item Name',
            'C9'=>'Unit',
            'D9'=>'Price',
            'E9'=>'Type',
            'F9'=>'Category'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ItemList'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'Item List');
        if(!empty($items)){
            $index = 11;
            $count = 1;
            foreach($items as $item){

                $itemName = $item->getItemName() ? $item->getItemName() : "...";
                $unit = $item->getItemUnit() ? $item->getItemUnit() : "...";
                $price = $item->getPrice() ? $item->getPrice() : "0";
                $type = $item->getItemType() ? $item->getItemType()->getItemType() : "...";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $itemName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $unit);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $price);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $type);

                foreach($item->getCategory() as $itemCategory){

                    $category = $itemCategory->getCategoryName() ? $itemCategory->getCategoryName() : "...";

                    $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $category);
                    $index++;
                }

                $index++;
                $index = $index - 1;
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

        foreach($header_arrays as $key => $header_array ){

            $objPHPExcel->getActiveSheet()->setCellValue($key, $header_array);
            $objPHPExcel->getActiveSheet()->getStyle($key)->applyFromArray($redArr);
            $objPHPExcel->getActiveSheet()->getColumnDimension($key[0])->setWidth(22);
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(15);
        }

        return $objPHPExcel;
    }

    public function updateAction(Request $request, Item $item)
    {
        $url = $request->server->get('REDIRECT_QUERY_STRING');
        $form = $this->createForm(new ItemType(), $item);
        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->update($item);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Item Add Successfully'
                );
               return $this->redirect($url,'301');
            }
        }

        return $this->render('PmsSettingBundle:Item:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Item $item)
    {
        $item->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->update($item);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Item Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('item', array('status' => 'active')));
    }

    public function activeAction(Item $item)
    {
        $item->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->update($item);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Item Successfully Restored'
        );

        return $this->redirect($this->generateUrl('item', array('status' => 'active')));
    }

    public function checkAction(Request $request)
    {
        $itemName = $request->request->get('itemName');

        $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->findOneBy(
            array('itemName' => $itemName )
        );

        if ($item) {
            $return = array("responseCode" => 200, "item_name" => "Item name already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "item_name" => "Item name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
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

    public function itemAutoCompleteForPrAction(Request $request){

        $itemQuery = array();
        $itemName = $request->query->get('q', null);
        $category = $request->query->get('categoryId',null);

        $item = $request->query->get('item',  null);

        if ($itemName) {
            $itemQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')
                ->itemAutoCompleteForPr($itemName, $this->getUser(),$category);

        } elseif ($item) {
          //  $priItem = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')->find($item);

          //  $item = $priItem->getItem()->getId();
            $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($item);

            $itemText = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')
                ->itemAutoCompleteForPr($item->getItemName(), $this->getUser(),$category);

            $itemQuery = array('id' => $itemText[0]['id'], 'text' => $itemText[0]['text']);

        }

        return new JsonResponse($itemQuery);
    }
} 