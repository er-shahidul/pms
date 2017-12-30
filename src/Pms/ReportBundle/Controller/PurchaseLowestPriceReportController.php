<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\CompareLowestPriceSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PurchaseLowestPriceReportController extends Controller
{
    public function compareLowestPriceSearchForm($request)
    {
        $form = new CompareLowestPriceSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function purchaseLowestPriceAction(Request $request)
    {
        list($form, $data) = $this->compareLowestPriceSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $compareLastPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseLastPrice($data);

        $itemIds = array_keys($compareLastPriceReport);

        $compareLowestPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseLowestPriceOfItems($itemIds, $data);
        $compareHighestPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseHighestPrice($data);
//var_dump($compareLowestPriceReport);
        //exit;

        foreach($compareLastPriceReport as $id => $row) {
            if(isset($compareLowestPriceReport[$id])) {
                $compareLastPriceReport[$id]['lowest'] = $compareLowestPriceReport[$id];
            }
            if(isset($compareHighestPriceReport[$id])) {
                $compareLastPriceReport[$id]['highest'] = $compareHighestPriceReport[$id];
            }
        }

        return $this->render('PmsReportBundle:Report:compareLowestPriceReport.html.twig', array(
            'compareLastPrice' => $compareLastPriceReport,
            'form' => $form->createView(),
        ));
    }

    public function purchaseLowestPriceExcelAction(Request $request)
    {
        list($form, $data) = $this->compareLowestPriceSearchForm($request);

        $compareLastPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseLastPrice($data);
        $compareLowestPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseLowestPrice($data);
        $compareHighestPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseHighestPrice($data);

        foreach($compareLastPriceReport as $id => $row) {
            if(isset($compareLowestPriceReport[$id])) {
                $compareLastPriceReport[$id]['lowest'] = $compareLowestPriceReport[$id];
            }
            if(isset($compareHighestPriceReport[$id])) {
                $compareLastPriceReport[$id]['highest'] = $compareHighestPriceReport[$id];
            }
        }
     //   var_dump($compareLastPriceReport);die;
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item',
            'C9'=>'Item Type ',
            'D9'=>'Date',
            'E9'=>'Month',
            'F9'=>'Brand',
            'G9'=>'Project',
            'H9'=>'Po No',
            'I9'=>'Issued By',
            'J9'=>'Last Price',

            'K9'=>'Date',
            'L9'=>'Month',
            'M9'=>'Brand',
            'N9'=>'Project',
            'O9'=>'Po No',
            'P9'=>'Issued By',
            'Q9'=>'Highest Price',

            'R9'=>'Date',
            'S9'=>'Month',
            'T9'=>'Brand',
            'U9'=>'Project',
            'V9'=>'Po No',
            'W9'=>'Issued By',
            'X9'=>'Lowest Price',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ItemReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("L5", 'Lowest Price Report');
        if(!empty($compareLastPriceReport)){
            $index = 11;
            $count = 1;
            foreach($compareLastPriceReport as $itemUse){
//                var_dump($itemUse);die;
                $dateObj = new \DateTime($itemUse['createdDate']);
                $dateObjHighest = $itemUse['highest']['createdDate'];
                $dateObjLowest = isset($itemUse['lowest']['createdDate']) ? $itemUse['lowest']['createdDate']:"";

                $item = $itemUse['itemName'] ? $itemUse['itemName'] : "";
                $itemType = $itemUse['itemTypeName'] ? $itemUse['itemTypeName']:"";
                $date = $dateObj->format('Y-m-d') ? $dateObj->format('Y-m-d') : " ";
                $month = $dateObj->format('F-Y') ? $dateObj->format('F-Y') : " ";
                $brand = $itemUse['brand'] ? $itemUse['brand'] : " ";
                $project = $itemUse['projectName'] ? $itemUse['projectName'] : " ";
                $po = $itemUse['poNo'] ? $itemUse['poNo'] : " ";
                $issuedBy = $itemUse['username'] ? $itemUse['username'] : " ";
                $lastPrice = $itemUse['price'] ? $itemUse['price'] : " ";

                /*highest */

                $dateHighest = $dateObjHighest->format('Y-m-d') ? $dateObjHighest->format('Y-m-d') : " ";
                $monthHighest = $dateObjHighest->format('F-Y') ? $dateObjHighest->format('F-Y') : " ";
                $brandHighest = $itemUse['highest']['brand'] ? $itemUse['highest']['brand'] : " ";
                $projectHighest = $itemUse['highest']['projectName'] ? $itemUse['highest']['projectName'] : " ";
                $poHighest = $itemUse['highest']['poNo'] ? $itemUse['highest']['poNo'] : " ";
                $issuedByHighest = $itemUse['highest']['username'] ? $itemUse['highest']['username'] : " ";
                $HighestPrice = $itemUse['highest']['price'] ? $itemUse['highest']['price'] : " ";

                $dateLowest = $dateObjLowest ?  $dateObjLowest->format('Y-m-d') : " ";
                $monthLowest = $dateObjLowest ? $dateObjLowest->format('F-Y') : " ";
                $brandLowest = isset($itemUse['lowest']) ? $itemUse['lowest']['brand']  : " ";
                $projectLowest = isset($itemUse['lowest']) ? $itemUse['lowest']['projectName'] : " ";
                $poLowest = isset($itemUse['lowest']) ? $itemUse['lowest']['poNo']  : " ";
                $issuedByLowest = isset($itemUse['lowest']) ?  $itemUse['lowest']['username'] : " ";
                $lowestPrice = isset($itemUse['lowest']) ?  $itemUse['lowest']['price']  : " ";



                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $item);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $itemType);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $date);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $month);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $brand);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $project);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $po);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $issuedBy);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $lastPrice);

                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $dateHighest);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $monthHighest);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $brandHighest);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $projectHighest);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $poHighest);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$index, $issuedByHighest);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$index, $HighestPrice);

                $objPHPExcel->getActiveSheet()->setCellValue("R".$index, $dateLowest);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$index, $monthLowest);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$index, $brandLowest);
                $objPHPExcel->getActiveSheet()->setCellValue("U".$index, $projectLowest);
                $objPHPExcel->getActiveSheet()->setCellValue("V".$index, $poLowest);
                $objPHPExcel->getActiveSheet()->setCellValue("W".$index, $issuedByLowest);
                $objPHPExcel->getActiveSheet()->setCellValue("X".$index, $lowestPrice);



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