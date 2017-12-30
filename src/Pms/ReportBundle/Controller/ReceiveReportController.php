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
class ReceiveReportController extends Controller
{
    public function receiveItemReportSearchForm($request)
    {
        $form = new ItemReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function receiveReportAction(Request $request)
    {

        list($form, $data) = $this->receiveItemReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $receiveReports = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')->receiveReport($data);
//        var_dump($receiveReports);die;

        return $this->render('PmsReportBundle:Report:receiveReport.html.twig', array(
            'receiveReports' => $receiveReports,
            'form' => $form->createView(),
        ));
    }

    public function receiveReportExcelAction(Request $request)
    {

        list($form, $data) = $this->receiveItemReportSearchForm($request);

        $receiveReports = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem')->receiveReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'Sl',
            'B9'=>'Item Name',
            'C9'=>'Unit',
            'D9'=>'Project',
            'E9'=>'Brand',
            'F9'=>'Received Qty',
            'G9'=>'Date',
            'H9'=>'GRN No',
            'I9'=>'Unit price',
            'J9'=>'Vendor/Buyer',
            'K9'=>'PO Issued',
            'L9'=>'PO No',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ReceiveReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("E5", 'Receive Report');
        if(!empty($receiveReports)){
            $index = 11;
            $count = 1;
            foreach($receiveReports as $itemUse){

                $item = $itemUse->getItem() ? $itemUse->getItem()->getItemName() : "";
                $unit = $itemUse->getItem() ? $itemUse->getItem()->getItemUnit() : "";
                $project = $itemUse->getProject() ? $itemUse->getProject()->getProjectName() : "";
                $brand = $itemUse->getPurchaseOrderItem() ? $itemUse->getPurchaseOrderItem()->getBrand() : "";
                $unitPrice = $itemUse->getPurchaseOrderItem() ? $itemUse->getPurchaseOrderItem()->getPrice() : "";
                $poIssued = $itemUse->getPurchaseOrderItem()->getPurchaseOrder()
                    ->getCreatedBy() ? $itemUse->getPurchaseOrderItem()
                    ->getPurchaseOrder()->getCreatedBy()->getUsername() : "";
                $vendor = $itemUse->getReceive()->getVendor() ? $itemUse->getReceive()->getVendor()->getVendorName() : "";
                $buyer = $itemUse->getReceive()->getBuyer() ? $itemUse->getReceive()->getBuyer()->getUsername() : "";

                if($vendor != ''){
                    $veOrBu = $vendor;
                }else{
                    $veOrBu = $buyer;
                }

                $qty = $itemUse->getQuantity() ? $itemUse->getQuantity() : "";
                $date = $itemUse->getReceive() ? $itemUse->getReceive()->getReceivedDate()->format('d/m/Y') : "";
                $grn = $itemUse->getReceive() ? $itemUse->getReceive()->getId() : "";

                $poNo = $itemUse->getPurchaseOrderItem()->getPurchaseOrder() ? $itemUse->getPurchaseOrderItem()->getPurchaseOrder()->getId() : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $item);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $unit);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $brand);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $qty);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $date);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $grn);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $unitPrice);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $veOrBu);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $poIssued);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $poNo);

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