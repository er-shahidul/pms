<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\SettingBundle\Entity\Vendor;
use Pms\SettingBundle\Entity\VendorAttach;
use Pms\SettingBundle\Form\VendorAttachType;
use Pms\SettingBundle\Form\VendorType;

use Pms\SettingBundle\Form\SearchForm\VendorSearchType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vendor controller.
 *
 */
class VendorController extends Controller
{
    public function vendorSearchForm($request)
    {
        $form = new VendorSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function indexAction(Request $request, $status = 'active')
    {
        list($form, $data) = $this->vendorSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $vendorRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor');

        $vendors = $this->paginate($vendorRepository->getVendorSearch($data, $status));


        return $this->render('PmsSettingBundle:Vendor:home.html.twig', array(
            'vendors' => $vendors,
            'status' => $status,
            'formSearch' => $form->createView(),
        ));
    }

    public function vendorsListExcelAction(Request $request)
    {
        list($form, $data) = $this->vendorSearchForm($request);

        $vendorRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor');
        $vendors = $this->paginateAll($vendorRepository->getVendorSearch($data, $status ='active'));

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Vendor Name',
            'c9'=>'Payment Type',
            'D9'=>'Contact Person',
            'E9'=>'Contact No',
            'F9'=>'Email',
            'G9'=>'Trade License No',
            'H9'=>'Tin Certificate No',
            'I9'=>'VAT Certificate No',
            'J9'=>'Bank Account Name',
            'K9'=>'Bank Account No',
            'L9'=>'Branch Name',
            'M9'=>'Area',
            'N9'=>'Address',
            'O9'=>'Item Type'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'VendorList'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("F5", 'Vendor List');
        if(!empty($vendors)){
            $index = 11;
            $count = 1;
            foreach($vendors as $vendor){

                $vendorName = $vendor->getVendorName() ? $vendor->getVendorName() : "...";
                $paymentType = $vendor->getPaymentType() ? $vendor->getPaymentType() : "...";
                $contactPerson = $vendor->getContractPerson() ? $vendor->getContractPerson() : "...";
                $contactNo = $vendor->getContractNo() ? $vendor->getContractNo() : "...";
                $email = $vendor->getEmail() ? $vendor->getEmail() : "...";
                $tradeLicenseNo = $vendor->getTradeLicenseNo() ? $vendor->getTradeLicenseNo() : "...";
                $tinCertificateNo = $vendor->getTinCertificateNo() ? $vendor->getTinCertificateNo() : "...";
                $vatCertificateNo = $vendor->getVatCertificateNo() ? $vendor->getVatCertificateNo() : "...";
                $bankAccountName = $vendor->getBankAccountName() ? $vendor->getBankAccountName() : "...";
                $bankAccountNo = $vendor->getBankAccountNo() ? $vendor->getBankAccountNo() : "...";
                $branchName = $vendor->getBranchName() ? $vendor->getBranchName() : "...";
                $area = $vendor->getArea() ? $vendor->getArea()->getAreaName() : "...";
                $address = $vendor->getVendorAddress() ? $vendor->getVendorAddress() : "...";
                $itemType = array();
                foreach($vendor->getItemTypes() as $itemTypes){
                    $itemType []= $itemTypes->getItemType() ? $itemTypes->getItemType() :"...";
                }
                $itemTypeValue = implode(',',$itemType);

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $paymentType);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $contactPerson);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $contactNo);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $email);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $tradeLicenseNo);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $tinCertificateNo);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $vatCertificateNo);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $bankAccountName);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $bankAccountNo);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $branchName);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $area);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $address);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $itemTypeValue);

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

    public function addAction(Request $request)
    {
        $vendor = new Vendor();

       // $vendor->

        $form = $this->createForm(new VendorType(), $vendor);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $vendor->setCreatedBy($this->getUser());
                $vendor->setCreatedDate(new \DateTime());
                $vendor->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->create($vendor);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Vendor Add Successfully'
                );

                return $this->redirect($this->generateUrl('vendor'));
            }
        }

        return $this->render('PmsSettingBundle:Vendor:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function attachAction(Request $request, $id)
    {
        $vendorAttach = new VendorAttach();

        $form = $this->createForm(new VendorAttachType(), $vendorAttach);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $vendorId = $_POST['vendorId'];

                $vendorAttach->setVendor($this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->findOneById($vendorId));

                $this->getDoctrine()->getRepository('PmsSettingBundle:VendorAttach')->create($vendorAttach);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Vendor Attach Upload Successfully'
                );

                return $this->redirect($this->generateUrl('vendor'));
            }
        }

        return $this->render('PmsSettingBundle:Vendor:attachForm.html.twig', array(
            'form' => $form->createView(),
            'id' => $id,
        ));
    }

    public function deleteAction(Vendor $vendor)
    {
        $vendor->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->update($vendor);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Vendor Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('vendor'));
    }

    public function activeAction(Vendor $vendor)
    {
        $vendor->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->update($vendor);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Vendor Successfully Restored'
        );

        return $this->redirect($this->generateUrl('vendor'));
    }

    public function updateAction(Request $request, Vendor $vendor)
    {
        $form = $this->createForm(new VendorType(), $vendor);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->update($vendor);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Vendor Update Successfully'
                );

                return $this->redirect($this->generateUrl('vendor'));
            }
        }

        return $this->render('PmsSettingBundle:Vendor:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function checkAction(Request $request)
    {
        $vendorName = $request->request->get('vendorName');

        $vendorCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->findOneBy(
            array('vendorName' => $vendorName )
        );

        if ($vendorCheck) {
            $return = array("responseCode" => 200, "vendor_name" => "Vendor already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "vendor_name" => "Vendor name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function vendorAddressAction(Request $request)
    {

        $vendorName = $request->request->get('vendorName');

        $vendor = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->find($vendorName);

        if ($vendor) {
            $vendorAddress = $vendor->getVendorAddress();
            $email = $vendor->getEmail();
            $contractPerson = $vendor->getContractPerson();
            $contractNo = $vendor->getContractNo();
            $data = array(
                'responseCode' => 200,
                'vendorAddress' => $vendorAddress,
                'email' => $email,
                'contractPerson' => $contractPerson,
                'contractNo' => $contractNo
            );

            $response = new Response(json_encode($data));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    public function vendorAutoCompleteAction(Request $request)
    {
        $vendorQuery = array();
        $vendorName = $request->query->get('q', null);
        $vendor = $request->query->get('vendor',  null);
        if ($vendorName) {
            $vendorQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')
                ->vendorAutoComplete($vendorName, $this->getUser());

        } else if ($vendor) {
            $vendor = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->find($vendor);
            $vendorQuery = array('id' => $vendor->getId(), 'text' => $vendor->getVendorName());
        }

        return new JsonResponse($vendorQuery);
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

    public function paginateAll($dql)
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
            8000/*limit per page*/
        );

        return $value;
    }

    public function attachmentViewAction(VendorAttach $vendorAttach)
    {

        if(null !== $response = $this->checkAttachFile($vendorAttach)) {
            return $response;
        }

        return $this->render('PmsSettingBundle:Vendor:viewer.html.twig', array(
            'vendor' => $vendorAttach,
            'path' => $vendorAttach->getPath(),
        ));
    }

    private function checkAttachFile(VendorAttach $vendorAttach)
    {
        if (null == $fileName = $vendorAttach->getAbsolutePath()) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function detailsAction(Vendor $vendor)
    {
        $vendorDetails = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->getVendorDetail($vendor);

        return $this->render('PmsSettingBundle:Vendor:details.html.twig', array(
            'vendor' => $vendorDetails,
        ));
    }
    public function mobileDetailsAction(Vendor $vendor)
    {
        $vendorDetails = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->getVendorDetail($vendor);

        return $this->render('PmsSettingBundle:Vendor:detailsForMobile.html.twig', array(
            'vendor' => $vendorDetails,
        ));
    }
} 