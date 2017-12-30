<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\BetweenTwoDateSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class ProjectPurchaseTypeWiseCountController extends Controller
{
    public function projectSpendPurchaseTypeWiseSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function projectPurchaseTypeWiseCountAction(Request $request)
    {
        list($form, $data) = $this->projectSpendPurchaseTypeWiseSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseOrderItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->projectPurchaseTypeWiseCountReport($data);
        $purchaseTypes  = $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->findAllPurchaseType();

        return $this->render('PmsReportBundle:Report:projectPurchaseTypeWiseCountReport.html.twig', array(
            'form' => $form->createView(),
            'purchaseOrderItems' => $purchaseOrderItems,
            'purchaseTypes' => $purchaseTypes,
        ));
    }

    public function projectPurchaseTypeWiseCountExcelAction(Request $request)
    {
        list($form, $data) = $this->projectSpendPurchaseTypeWiseSearchForm($request);

        $purchaseOrderItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->projectPurchaseTypeWiseCountReport($data);
        $purchaseTypes  = $this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->findAllPurchaseType();

        $header_arrays = array();
        $header_arrays['A9'] = 'S/L';
        $header_arrays['B9'] = 'Project Name';
        $c = 'C';
        $p = 9;
        $k = 0;
        foreach($purchaseTypes as $purchaseType){
            $header_arrays[$c.$p] = $purchaseType['name'];
            $c++;
            $k++;
        }
        $header_arrays[$c.$p] = 'Total';

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ProjectReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Project Report');
        if(!empty($purchaseOrderItems)){
            $index = 11;
            $count = 1;
            foreach($purchaseOrderItems as $projectCostItem){

                $total = $projectCostItem['total'] ? $projectCostItem['total'] : "0";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $projectCostItem['projectName']);

                $car = 'C';
                $array_keys =  $projectCostItem['typeSummary'];

                foreach ($purchaseTypes as $purchaseType) {

                    if (isset($array_keys[$purchaseType['id']])) {
                        $objPHPExcel->getActiveSheet()->setCellValue($car . $index, $array_keys[$purchaseType['id']]);
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellValue($car . $index, '0');
                    }
                    $car++;
                }

                $objPHPExcel->getActiveSheet()->setCellValue($c.$index, $total);

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