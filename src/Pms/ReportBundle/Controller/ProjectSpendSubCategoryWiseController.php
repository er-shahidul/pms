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
class ProjectSpendSubCategoryWiseController extends Controller
{
    public function projectSpendSubCategoryWiseSearchForm($request)
    {
        $form = new BetweenTwoDateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function projectSpendSubCategoryWiseAction(Request $request)
    {
        list($form, $data) = $this->projectSpendSubCategoryWiseSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $purchaseOrderItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->projectSpendSubCategoryWiseReport($data);

        $subCategories = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->findAllSubCategory();

        return $this->render('PmsReportBundle:Report:projectSpendSubCategoryWiseReport.html.twig', array(
            'form' => $form->createView(),
            'purchaseOrderItems' => $purchaseOrderItems,
            'subCategories' => $subCategories,
            'data' => $data,
        ));
    }

    public function projectSpendSubCategoryWiseExcelAction(Request $request)
    {
        list($form, $data) = $this->projectSpendSubCategoryWiseSearchForm($request);

        $purchaseOrderItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->projectSpendSubCategoryWiseReport($data);
        $subCategories = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->findAllSubCategory();

        $header_arrays = array();
        $header_arrays['A9'] = 'S/L';
        $header_arrays['B9'] = 'Project Name';
        $header_arrays['C9'] = 'Area';
        $c = 'D';
        $p = 9;
        $k = 0;
        foreach($subCategories as $purchaseType){
            $header_arrays[$c.$p] = $purchaseType['subCategoryName'];
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
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $projectCostItem['areaName']);

                $car = 'D';
                $array_keys =  $projectCostItem['typeSummary'];

                foreach ($subCategories as $purchaseType) {

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

    public function projectSpendSubCategoryWiseItemDetailsAction(Request $request)
    {
        $data = $_REQUEST;
        $purchaseOrderItems = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPurchaseTypeSubCategoryItemWise($data);
        $subCategory = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->find($data['subCategory']);
        $projectName = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($data['project']);

        return $this->render('PmsReportBundle:Report:projectSpendSubCategoryWisePoItemDetailsReport.html.twig', array(
            'poItemDetails' => $purchaseOrderItems,
            'projectName' => $projectName,
            'subCategory' => $subCategory,
        ));
    }

    public function projectSpendSubCategoryWiseItemDetailAction(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $project = $request->get('projectId');
        $detailSpendAmount = $this->getDoctrine()
                                   ->getRepository('PmsCoreBundle:PurchaseOrder')
                                   ->projectSpendSubCategoryWiseReportDetail($project,$start_date,$end_date);
//var_dump($detailSpendAmount);die;
        return $this->render('PmsReportBundle:Report:projectSpendSubCategoryWiseReportDetail.html.twig', array(
            'projectSpendSubCategoryWiseAmounts' => $detailSpendAmount,
        ));
    }
} 