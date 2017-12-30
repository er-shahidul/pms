<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\VendorStatusReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class VendorStatusReportController extends Controller
{
    public function vendorStatusReportSearchForm($request)
    {
        $form = new VendorStatusReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function vendorStatusReportAction(Request $request)
    {
        list($form, $data) = $this->vendorStatusReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $budgetDailies = $this->paginate($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getVendorStatusReport($data));

        return $this->render('PmsReportBundle:Report:vendorStatus.html.twig', array(
            'budgetDailys' => $budgetDailies,
            'form' => $form->createView(),
        ));
    }

    public function vendorStatusTwoReportAction(Request $request)
    {
        list($form, $data) = $this->vendorStatusReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $data['vendor'] = $request->query->get('vendor');

        $poInFo = $this->getDoctrine()
                        ->getRepository('PmsCoreBundle:PurchaseOrder')
                        ->getPoInFo($data);

        $paymentInFo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Payment')
            ->getPaymentInFo($data);

        $grnInfo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->getGrnInfo($data);

        $paymentRequestInFo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:ReadyForPayment')
            ->getPaymentRequestAmount($data);


        $vendorPaymentList = array(
            'vendorName'=>$poInFo?$poInFo[0]['vendorName']:'',
            'noOfOrder'=>$poInFo?$poInFo[0]['poId']:' ',
            'totalPoAmount'=>$poInFo?$poInFo[0]['PoTotal']:'',
            'grnTotal'=>$grnInfo[0] ? $grnInfo[0]['GrnAmount']:'',
            'advanceAmount'=>$poInFo?$poInFo[0]['advancePayment']:'',
            'adjustmentAmount'=>$paymentInFo?$paymentInFo[0]['adjustmentAmount']:'',
            'paymentAmount'=>$paymentInFo[0] ? $paymentInFo[0]['paymentAmount']:'',
        );
        $vendorPaymentList['totalPaymentAmount'] = $vendorPaymentList['paymentAmount'] + $vendorPaymentList['adjustmentAmount'];
        $vendorPaymentList['requestedAmount'] = $vendorPaymentList['grnTotal']+ $vendorPaymentList['advanceAmount'];
        $vendorPaymentList['dueAmount'] = $vendorPaymentList['requestedAmount']- $vendorPaymentList['totalPaymentAmount'];

        return $this->render('PmsReportBundle:Report:vendorStatusTwo.html.twig', array(
            'vendorPaymentList' => $vendorPaymentList,
            'form' => $form->createView(),
            'vendor'=>$data
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

    public function vendorStatusReportExcelAction(Request $request)
    {
        list($form, $data) = $this->vendorStatusReportSearchForm($request);

        $budgetDailys = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getVendorStatusReportExcel($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Project',
            'C9'=>'Type',
            'D9'=>'Vendor',
            'E9'=>'PO No',
            'F9'=>'PO Date',
            'G9'=>'Delivery Date',
            'H9'=>'Bill Date',
            'I9'=>'Payment Date',
            'J9'=>'PO Issued By',
            'K9'=>'ContactPerson',
            'L9'=>'Phone',
            'M9'=>'PO Remarks',
            'N9'=>'Receive Remarks'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'VendorPerformanceOverview'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Vendor Performance Overview');
        if(!empty($budgetDailys)){
            $index = 10;
            $count = 1;
            foreach($budgetDailys as $budgetDaily){

                $project = $budgetDaily->getProject()->getProjectName() ? $budgetDaily->getProject()->getProjectName() : "";
                $date = $budgetDaily->getPurchaseOrder()->getCreatedDate() ? $budgetDaily->getPurchaseOrder()->getCreatedDate()->format('d-F-Y') : "";
                $deliveryDate = $budgetDaily->getPurchaseOrder()->getDateOfDelivered() ? $budgetDaily->getPurchaseOrder()->getDateOfDelivered()->format('d-F-Y') : "";
                $poIssuedBy = $budgetDaily->getPurchaseOrder()->getCreatedBy() ? $budgetDaily->getPurchaseOrder()->getCreatedBy()->getUsername() : "";
                $poNo = $budgetDaily->getPurchaseOrder()->getId() ? $budgetDaily->getPurchaseOrder()->getId() : "";
                $type = $budgetDaily->getPurchaseOrder()->getPoNonpo() ? $budgetDaily->getPurchaseOrder()->getPoNonpo()->getName() : "";
                $vendor = $budgetDaily->getPurchaseOrder()->getVendor()->getVendorName() ? $budgetDaily->getPurchaseOrder()->getVendor()->getVendorName() : "";
                $contactPerson = $budgetDaily->getPurchaseOrder()->getVendor()->getContractPerson() ? $budgetDaily->getPurchaseOrder()->getVendor()->getContractPerson() : "";
                $phone = $budgetDaily->getPurchaseOrder()->getVendor()->getContractNo() ? $budgetDaily->getPurchaseOrder()->getVendor()->getContractNo() : "";
                $poRemarks = $budgetDaily->getPurchaseOrder()->getComment() ? $budgetDaily->getPurchaseOrder()->getComment() : "";

                $billDate = array();
                foreach($budgetDaily->getPurchaseOrder()->getReadyForPayments() as $rfp){
                        $billDate[] =  $rfp->getBillDate() ? $rfp->getBillDate()->format('d-m-Y') : "";
                }
                $billDate = implode (", ", $billDate);

//                $paymentDate = array();
//                foreach($budgetDaily->getPurchaseOrder()->getReadyForPayments() as $rfp){
//                    if(!$rfp->getPayment()->getPaymentDate() instanceof \DateTime) {
//                        die("thats not done");
//                    }
//                    $paymentDate[] =  $rfp->getPayment() ? $rfp->getPayment()->getPaymentDate()->format('d-m-Y') : "";
//                }
//                $paymentDate = implode (", ", $paymentDate);

                $paymentDate = array();
                foreach($budgetDaily->getPurchaseOrder()->getReadyForPayments() as $rfp){
                    $paymentDate[] =  $rfp->getPayment() && $rfp->getPayment()->getPaymentDate() ? $rfp->getPayment()->getPaymentDate()->format('d-m-Y') : "";
                }
                $paymentDate = implode (", ", $paymentDate);

                $receiveRemarks = array();
                foreach($budgetDaily->getReceivedItems() as $ri){
                        $receiveRemarks[] =  $ri->getComment() ? $ri->getComment() : "";
                }
                $receiveRemarks = implode (", ", $receiveRemarks);

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $type);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $vendor);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $poNo);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $date);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $deliveryDate);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $billDate);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $paymentDate);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $poIssuedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $contactPerson);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $phone);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $poRemarks);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $receiveRemarks);
                $index++;
                $count++;
            }
//            $objPHPExcel->getActiveSheet()->setCellValue("F".$index, 'Total :');
//            $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $budgetDailyTotal[0]['total']);
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

    public function vendorStatusTwoReportExcelAction(Request $request)
    {
        list($form, $data) = $this->vendorStatusReportSearchForm($request);

        $budgetDailys = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
                             ->getVendorStatusTwoReportExcel($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Vendor Name',
            'C9'=>'No of Purchase Order',
            'D9'=>'Total PO Amount',
            'E9'=>'Paid Amount',
            'F9'=>'Dues Amount'
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'VendorPaymentOverview'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Vendor Payment Overview');
        if(!empty($budgetDailys)){
            $index = 10;
            $count = 1;
            foreach($budgetDailys as $budgetDaily){

                $vendorName = $budgetDaily['vendorName'] ? $budgetDaily['vendorName'] : "";
                $totalPo = $budgetDaily['totalPo'] ? $budgetDaily['totalPo'] : 0;
                $total = $budgetDaily['total'] ? $budgetDaily['total'] : 0;
                $paymentAmount = $budgetDaily['paymentAmount'] ? $budgetDaily['paymentAmount'] : 0;
                $duesAmount = $total - $paymentAmount;

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $totalPo);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $total);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $paymentAmount);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $duesAmount);
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
    public function vendorStatusTwoReportDetailAction(Request $request)
    {
        $vendor = $request->get('vendor');

        $poInFo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrder')
            ->getPurchaseOrderByVendor($vendor);

        $paymentInFo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Payment')
            ->getPaymentByVendor($vendor);

        $grnInfo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->getGrnByVendor($vendor);

        $poList = array();

        foreach ($poInFo as $id => $row) {

            $details = array(
                'PONumber'              => $row['poId'],
                'POIssueDate'           => $row['poIssueDate']->format('Y-m-d'),
                'POIssueMonth'          => $row['poIssueDate']->format('F'),
                'POAmount'              => isset($row['PoTotal']) ? $row['PoTotal']:0,
                'GrnAmount'             => isset($grnInfo[$id]) ? $grnInfo[$id]['GrnAmount']:0,
                'AdvanceAmount'         => isset($row['advancePayment']) ? $row['advancePayment']:0,
                'PaymentAmount'         => isset($paymentInFo[$id]) ? $paymentInFo[$id]['paymentAmount'] : 0,
                'BankName'              => isset($paymentInFo[$id]) ? $paymentInFo[$id]['bankName'] : '',
                'ChequeNumber'          => isset($paymentInFo[$id]) ? $paymentInFo[$id]['chequeNumber'] : '',
            );
            $poList[] = $details;
        }
        return $this->render(
            'PmsReportBundle:Report:vendorStatusTwoDetail.html.twig',array(
            'poLists'=>$poList,
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
            25/*limit per page*/
        );

        return $value;
    }
} 