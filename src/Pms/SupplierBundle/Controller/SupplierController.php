<?php

namespace Pms\SupplierBundle\Controller;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\SupplierBundle\Entity\Supplier;
use Pms\SupplierBundle\Entity\SupplierDocument;
use Pms\SupplierBundle\Form\SupplierSearchType;
use Pms\SupplierBundle\Form\SupplierType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SupplierController extends Controller
{
    public function indexAction(Request $request)
    {
        list($form, $data) = $this->supplierSearchForm($request);
// var_dump($data);die;
        $form = $this->createForm($form);

        $form->submit($data);

       // $list = $this->paginate($this->getDoctrine()->getRepository('PmsSupplierBundle:Supplier')->findBy(array(),array('createdDate'=>'DESC')));
        $list = $this->paginate($this->getDoctrine()->getRepository('PmsSupplierBundle:Supplier')->getAllSupplier($data));

        return $this->render('PmsSupplierBundle:Supplier:home.html.twig',
            array(
                'SupplierLists' =>$list,
                'form' => $form->createView(),
            ));
    }
    public function createAction(Request $request)
    {
        $Supplier = new Supplier();

        $form = $this->createForm(new SupplierType(), $Supplier);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $Supplier->setCreatedBy($this->getUser());
                $Supplier->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));

                $this->getDoctrine()->getRepository('PmsSupplierBundle:Supplier')->create($Supplier);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Supplier Add Successfully'
                );
                return $this->redirect($this->generateUrl('pms_supplier_homepage'));
            }
        }
        return $this->render('PmsSupplierBundle:Supplier:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    public function updateSupplierAction(Request $request,Supplier $supplier)
    {
//        $Supplier = new Supplier();

        $form = $this->createForm(new SupplierType(), $supplier);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $supplier->setCreatedBy($this->getUser());
                $supplier->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));

                $this->getDoctrine()->getRepository('PmsSupplierBundle:Supplier')->create($supplier);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Supplier Add Successfully'
                );
                return $this->redirect($this->generateUrl('pms_supplier_homepage'));
            }
        }
        return $this->render('PmsSupplierBundle:Supplier:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function detailsAction(Supplier $supplier)
    {
        return $this->render('PmsSupplierBundle:Supplier:details.html.twig', array(
            'supplierDetail' => $supplier
        ));
    }

    public function detailsForMobileAction(Supplier $supplier)
    {

        return $this->render('PmsSupplierBundle:Supplier:detailsForMobile.html.twig', array(
            'supplierDetail' => $supplier
        ));
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



    public function supplierSearchForm($request)
    {

        $form = new SupplierSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function supplierExcelAction(Request $request)
    {
        list($form, $data) = $this->supplierSearchForm($request);

        $lists = $this->getDoctrine()->getRepository('PmsSupplierBundle:Supplier')->getAllSupplier($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Created Date',
            'C9'=>'Month',
            'D9'=>'Item',
            'E9'=>'Item Type',
            'F9'=>'Category',
            'G9'=>'Sub Category',
            'H9'=>'Supplier Name',
            'I9'=>'Contact Person',
            'J9'=>'Email',
            'K9'=>'Created By',


        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'vendorSourcing'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Vendor Sourcing');
        if(!empty($lists)){
            $index = 11;
            $count = 1;
            foreach($lists as $list){


                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $list->getCreatedDate()->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $list->getCreatedDate()->format('F'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $list->getItem()?$list->getItem():'');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $list->getItemType()?$list->getItemType()->getItemType():'');
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $list->getCategory()?$list->getCategory()->getCategoryName():'');
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $list->getSubCategory()?$list->getSubCategory()->getSubCategoryName():'');
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $list->getName()?$list->getName():'');
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $list->getContactPerson()?$list->getContactPerson():'');
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $list->getEmail()?$list->getEmail():'');
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $list->getCreatedBy()?$list->getCreatedBy()->getUserName():'');

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

    public function vendorSourcingAttachmentViewAction(SupplierDocument $supplierDocument)
    {

        if(null !== $response = $this->checkAttachFile($supplierDocument)) {
            return $response;
        }

        return $this->render('PmsSupplierBundle:Supplier:viewer.html.twig', array(
            'vendor' => $supplierDocument,
            'path' => $supplierDocument->getPath(),
        ));
    }

    private function checkAttachFile(SupplierDocument $supplierDocument)
    {

        if (null == $fileName = $supplierDocument->getAbsolutePath()) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }
}
