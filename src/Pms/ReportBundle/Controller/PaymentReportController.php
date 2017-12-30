<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PaymentCompanyReportSearchType;
use Pms\ReportBundle\Form\RegularPaymentReportSearchType;
use Pms\ReportBundle\Form\VendorPaymentReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PaymentReportController extends Controller
{
    public function vendorPaymentReportSearchForm($request)
    {
        $form = new VendorPaymentReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }
    public function regularPaymentReportSearchForm($request)
    {
        $form = new RegularPaymentReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }
    public function paymentCompanyReportSearchForm($request)
    {
        $form = new PaymentCompanyReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function vendorPaymentStatusOneReportAction(Request $request)
    {

        list($form, $data) = $this->vendorPaymentReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $vendorPaymentsOne = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->vendorPaymentOneReport($data);

        return $this->render('PmsReportBundle:Report:vendorPaymentStatusOneReport.html.twig', array(
            'vendorPaymentsOne' => $vendorPaymentsOne,
            'form' => $form->createView(),
        ));
    }
    public function vendorPaymentStatusOneReportExcelAction(Request $request)
    {

        list($form, $data) = $this->vendorPaymentReportSearchForm($request);

        $vendorPaymentsOne = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->vendorPaymentOneReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Vendor Name',
            'C9'=>'No Of Order',
            'D9'=>'Total Po Amount',
            'E9'=>'Paid Amount',
            'F9'=>'Dues Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'VendorPaymentOneReport'.'.xlsx';
        $date = 'Date : '.$data['start_date'].' To '.$data['end_date'];

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'vendor Payment Status One Report');
        $objPHPExcel->getActiveSheet()->setCellValue("A6", $date);
        if(!empty($vendorPaymentsOne)){

            $index = 11;
            $count = 1;
            foreach($vendorPaymentsOne as $vendorPaymentOne){

                $vendorName = 'Vendor Name :'. $vendorPaymentOne['vendorName'];
                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $vendorName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $vendorPaymentOne['NoOfOrder']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $vendorPaymentOne['totalPoAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $vendorPaymentOne['paidAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $vendorPaymentOne['duesAmount']);

                $index++;
                $count++;
            }
            if($data['vendor']){
            $objPHPExcel->getActiveSheet()->setCellValue("A7", $vendorName);
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


    public function vendorPaymentStatusTwoReportAction(Request $request)
    {

        list($form, $data) = $this->vendorPaymentReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $vendorPaymentsTwo = $this->getDoctrine()
                                  ->getRepository('PmsCoreBundle:ReadyForPayment')
                                  ->vendorPaymentTwoReport($data);

        return $this->render('PmsReportBundle:Report:vendorPaymentStatusTwoReport.html.twig', array(
            'vendorPaymentsTwo' => $vendorPaymentsTwo,
            'form' => $form->createView(),
        ));
    }

    public function regularPaymentReportAction(Request $request)
    {

        list($form, $data) = $this->regularPaymentReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $regularPayments = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->getRegularPaymentInfo($data);

        return $this->render('PmsReportBundle:Report:regularPaymentReport.html.twig', array(
            'regularPayments' => $regularPayments,
            'form' => $form->createView(),
        ));
    }
    public function paymentCompanyReportAction(Request $request)
    {

        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $paymentAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->getPaymentAmountByCompanyTypeAndDate($data);
        $requestAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->getRequestAmount($data);
        $poAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getTotalPoAmountByMonthly($data);

        $vatCollection = $this->getVatCollectionMonthly($data);

        $month = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December');

        $array =array();

        if(!empty($requestAmount)){

                foreach($requestAmount as  $key => $reports){

                    $finalPaymentArray = array();
                    $finalPaymentArray['totalRequest'] = $reports['requestAmount'];
                    $finalPaymentArray['paymentAmount'] = $paymentAmount[$key]['paymentAmount'];
                    $finalPaymentArray['poAmount'] = $poAmount[$key]['poAmount'];
                    $finalPaymentArray['vat'] = $vatCollection[$key]['tax'];
                    $finalPaymentArray['DuesAmount'] = $reports['requestAmount'] - $paymentAmount[$key]['paymentAmount'];
                    $finalPaymentArray['monthNumber'] = $key;
                    $array[$key] = $finalPaymentArray;
                }
        }

        return $this->render('PmsReportBundle:Report:paymentCompanyReport.html.twig', array(
            'form' => $form->createView(),
            'month'=>$month,
            'paymentInfos'=>$array
        ));
    }

   /* public function paymentCompanyReportAction(Request $request)
    {

        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        //  $paymentCompany = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->getPaymentCompany($data);

        $requestAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->getRequestAmount($data);
//        var_dump($request);die;

        $vatCollection = $this->getVatCollectionMonthly($data);

        $month = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December');

        $array =array();
        if(!empty($paymentCompany)){

            foreach($paymentCompany as $reports){

                $finalPaymentArray = array();
                $countDays = 0;
                $totalRequestAmount = 0;
                $totalPaymentAmount = 0;

                foreach($reports as  $paymentCompaniesReport){

                    $fromDate = $paymentCompaniesReport['requestDate']->format('Y-m-d');
                    $toDate   = $paymentCompaniesReport['paymentDate']->format('Y-m-d');

                    $countDays += $this->differenceDate($fromDate, $toDate);

                    $finalPaymentArray['totalRequest']           = $totalRequestAmount += $paymentCompaniesReport['requestAmount'];
                    $finalPaymentArray['totalPayment']           = $totalPaymentAmount += $paymentCompaniesReport['paymentAmount'];
                    $finalPaymentArray['countDays']              = $countDays;
                    $finalPaymentArray['duesAmount']             = ($totalRequestAmount - $totalPaymentAmount);
                    $finalPaymentArray['monthlyCount']           = COUNT($paymentCompaniesReport);
                    $finalPaymentArray['paymentResolvedAverage'] = $countDays / $finalPaymentArray['monthlyCount'];

                }
                $array[] = $finalPaymentArray;

            }
        }

        return $this->render('PmsReportBundle:Report:paymentCompanyReport.html.twig', array(
            'form' => $form->createView(),
            'month'=>$month,
            'vat'=>$vatCollection,
            'paymentInfos'=>$array
        ));
    }*/

    public function paymentCompanyReport1Action(Request $request)
    {

        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

       // $paymentCompany = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->getPaymentCompany($data);

        $monthlyPoApprovedAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getMonthlyPoApprovedAmount($data);

        $monthlyReceiveAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->getMonthlyReceivedAmount($data);

        $monthlyPaymentAmount = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->getMonthlyPaymentAmount($data);
      //  var_dump($monthlyPoApprovedAmount);die;
        $vatCollection = $this->getVatCollectionMonthly($data);

        $month = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December');

        return $this->render('PmsReportBundle:Report:paymentCompanyReport.html.twig', array(
            'form' => $form->createView(),
            'month'=>$month,
            'vat'=>$vatCollection,
            'poApprovedAmount'=>$monthlyPoApprovedAmount,
            'GrnAmount'=>$monthlyReceiveAmount,
            'PaymentAmount'=>$monthlyPaymentAmount
        ));
    }

    public function paymentCompanyReportExcelAction(Request $request)
    {

        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $paymentCompany = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->getPaymentCompany($data);

        $month = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December');

        $array =array();

        foreach($paymentCompany as $reports){

            $finalPaymentArray =array();
            $countDays = 0;
            $totalRequestAmount = 0;
            $totalPaymentAmount = 0;

            foreach($reports as  $paymentCompaniesReport){

                $fromDate = $paymentCompaniesReport['requestDate']->format('Y-m-d');
                $toDate   = $paymentCompaniesReport['paymentDate']->format('Y-m-d');

                $countDays += $this->differenceDate($fromDate, $toDate);

                $finalPaymentArray['totalRequest']           = $totalRequestAmount += $paymentCompaniesReport['requestAmount'];
                $finalPaymentArray['totalPayment']           = $totalPaymentAmount += $paymentCompaniesReport['paymentAmount'];
                $finalPaymentArray['countDays']              = $countDays;
                $finalPaymentArray['duesAmount']             = ($totalRequestAmount - $totalPaymentAmount);
                $finalPaymentArray['monthlyCount']           = COUNT($paymentCompaniesReport);
                $finalPaymentArray['paymentResolvedAverage'] = $countDays / $finalPaymentArray['monthlyCount'];

            }
            $array[] = $finalPaymentArray;

        }

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'Month',
            'B9'=>'Request Amount',
            'C9'=>'Payment Amount',
            'D9'=>'Dues Amount',
            'E9'=>'Average Resolved Request',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'paymentCompany'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'Payment Company Amount');
        if(!empty($array)){

            $index = 11;
            $count = 0;

            foreach($array as $arrays){
                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $month[$count]);
                if(!empty($arrays)){
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $arrays['totalRequest']?$arrays['totalRequest']:'0');
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $arrays['totalPayment']?$arrays['totalPayment']:'0');
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $arrays['duesAmount']?$arrays['duesAmount']:'0');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $arrays['paymentResolvedAverage']?$arrays['paymentResolvedAverage']:'');
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

    public function differenceDate($fromDate,$toDate){
        $date1=date_create($fromDate);
        $date2=date_create($toDate);
        $diff=date_diff($date1,$date2);
        return $diff->format("%a");
    }

    public function vendorPaymentStatusTwoReportExcelAction(Request $request)
    {

        list($form, $data) = $this->vendorPaymentReportSearchForm($request);

        $vendorPaymentsTwo = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:ReadyForPayment')
            ->vendorPaymentTwoReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Vendor Name',
            'C9'=>'PO No',
            'D9'=>'Project Name',
            'E9'=>'Total Po Amount',
            'F9'=>'Payment Type',
            'G9'=>'Payment Mode',
            'H9'=>'Payment From',
            'I9'=>'Purchase Type ',
            'J9'=>'Paid Amount',
            'K9'=>'Bank Name',
            'L9'=>'Cheque No',
            'M9'=>'Bill No',
            'N9'=>'Payment Request Date',
            'O9'=>'Payment Date',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'VendorPaymentStatus2Report'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("E5", 'Vendor Payment Status 2 Report');
        if(!empty($vendorPaymentsTwo)){

            $index = 11;
            $count = 1;
            foreach($vendorPaymentsTwo as $vendorPaymentTwo){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $vendorPaymentTwo['vendorName']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $vendorPaymentTwo['poNo']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $vendorPaymentTwo['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $vendorPaymentTwo['totalPoAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("f".$index, $vendorPaymentTwo['paymentType']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $vendorPaymentTwo['paymentMode']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $vendorPaymentTwo['paymentFrom'] == 1 ? 'Local Office' : 'Head Office');
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $vendorPaymentTwo['purchaseType']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $vendorPaymentTwo['paidAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $vendorPaymentTwo['bankName']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $vendorPaymentTwo['chequeNo']);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $vendorPaymentTwo['billNumber']);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $vendorPaymentTwo['requestDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $vendorPaymentTwo['billDate']->format('Y-m-d'));

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
    public function regularPaymentExcelAction(Request $request)
    {

        list($form, $data) =  $this->regularPaymentReportSearchForm($request);;

        $regularPayments = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->getRegularPaymentInfo($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'ReceiveDate',
            'C9'=>'GRN#NO',
            'D9'=>'Project Name',
            'E9'=>'Vendor/Buyer',
            'F9'=>'ReceivedBy',
            'G9'=>'PR No',
            'H9'=>'PO NO',
            'I9'=>'PO Issued By',
            'J9'=>'Payment Form',
            'K9'=>'POAmount',
            'L9'=>'Receive Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'RegularPaymentReport'.'.xlsx';

        $sheet = $objPHPExcel->getActiveSheet();




        if(!empty($regularPayments)){


            $filteredTo = 'Date : '.$data['start_date'].' TO '.$data['end_date'];
            $sheet->setCellValue("E5", 'Regular Payment');
            $sheet->setCellValue("A5",$filteredTo);

            $index = 11;
            $count = 1;
            foreach($regularPayments as $regularPayment){
                $prno = array();
                foreach($regularPayment->getReceiveItems() as $prNo){
                    $prno[] = $prNo->getPurchaseOrderItem()->getPurchaseRequisitionItem()->getPurchaseRequisition()->getId();
                    $purchaseOrderId = $prNo->getPurchaseOrderItem()->getPurchaseOrder()->getId();
                    $poIssuedBy = $prNo->getPurchaseOrderItem()->getPurchaseOrder()->getCreatedBy()->getFullname();
                    $projectName = $prNo->getProject()->getProjectName();
                }

                    $multiplePrNo = implode(',',$prno);

                $sheet->setCellValue("A".$index, $count);
                $sheet->setCellValue("B".$index, $regularPayment->getReadyForPayment()->getRequestDate()->format('Y-m-d'));
                $sheet->setCellValue("C".$index, $regularPayment->getId());
                $sheet->setCellValue("D".$index, $projectName);
                $sheet->setCellValue("E".$index, $regularPayment->getVendor()->getVendorName());
                $sheet->setCellValue("F".$index, $regularPayment->getReceivedBy()->getFullName());
                $sheet->setCellValue("G".$index, $multiplePrNo);
                $sheet->setCellValue("H".$index, $purchaseOrderId);
                $sheet->setCellValue("I".$index, $poIssuedBy);
                $sheet->setCellValue("J".$index, $regularPayment->getIsHeadOrLocal());
                $sheet->setCellValue("K".$index, $regularPayment->getReadyForPayment()->getPoAmount());
                $sheet->setCellValue("L".$index, $regularPayment->getReadyForPayment()->getPoReceiveAmount());

                $index++;
                $count++;
            }

            $projectFilteredTo = 'Project :'.$projectName;
            $sheet->setCellValue("A6", $projectFilteredTo);

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

    /**
     * @param $data
     */
    private function getVatCollectionMonthly($data)
    {
        $vatCollectionMonthly = $this->getDoctrine()
                                     ->getRepository('PmsCoreBundle:PurchaseOrder')
                                     ->getPaymentDetail($data);
      //  var_dump($vatCollectionMonthly);die;
       /* $vatArray = array();
        if (!empty($vatCollectionMonthly)) {

            foreach ($vatCollectionMonthly as $vats) {
                $finalVatArray = array();
                $vat           = 0;
                foreach ($vats as $vatsTotal) {
                    $finalVatArray['tax'] = $vat += $vatsTotal['tax'];
                }
                $vatArray[] = $finalVatArray;
            }
        }
        var_dump($vatArray);die;*/
        return $vatCollectionMonthly;
    }




}