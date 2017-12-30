<?php

namespace Pms\ReportBundle\Controller;

use Pms\BudgetBundle\Form\SearchForm\DateSearchType;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class BudgetVsSpendReportController extends Controller
{
    public function budgetVsSpendReportSearchForm($request)
    {
        $form = new DateSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function budgetVsSpendReportAction(Request $request)
    {

        list($form, $data) = $this->budgetVsSpendReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $budgetVsSpends = $this->getDoctrine()->getRepository('PmsBudgetBundle:BudgetForSubCategory')->getBudgetVsSpendReport($data);

        $dataArray = array();
        foreach($budgetVsSpends as $key => $budgetVsSpend ){
            $dataArray[$key]['subCategoryName'] = $budgetVsSpend['subCategoryName'];
            $dataArray[$key]['budgetAmount'] = $budgetVsSpend['budgetAmountForSubCategory'];
            $dataArray[$key]['subCategoryID'] = $budgetVsSpend['subCategoryID'];
            $dataArray[$key]['month'] = $data['month'];
            $dataArray[$key]['spendAmount'] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getBudgetVsSpendsForPoiTotal($data, $budgetVsSpend['subCategoryID']);
        }

        $subCategoryNameArray = array();
        foreach($budgetVsSpends as $key => $budgetVsSpend){
            $subCategoryNameArray[] = $budgetVsSpend['subCategoryName'];
        }

        $budgetAmountArray = array();
        foreach($budgetVsSpends as $key => $budgetVsSpend){
            $budgetAmountArray[] = (int)$budgetVsSpend['budgetAmountForSubCategory'];
        }

        $spendAmountArray = array();
        foreach($dataArray as $key => $budgetVsSpend){
                $spendAmountArray[] = $budgetVsSpend['spendAmount'] ? (int)$budgetVsSpend['spendAmount'] : 0 ;
        }

        return $this->render('PmsReportBundle:Report:budgetVsSpend.html.twig', array(
            'dataArray' => $dataArray,
            'subCategoryNameArray' => json_encode($subCategoryNameArray),
            'budgetAmountArray' => json_encode($budgetAmountArray),
            'spendAmountArray' => json_encode($spendAmountArray),
            'form' => $form->createView(),
        ));
    }

    public function budgetVsSpendReportExcelAction(Request $request)
    {
        list($form, $data) = $this->budgetVsSpendReportSearchForm($request);

        $budgetVsSpends = $this->getDoctrine()->getRepository('PmsBudgetBundle:BudgetForSubCategory')->getBudgetVsSpendReport($data);

        $dataArray = array();
        foreach($budgetVsSpends as $key => $budgetVsSpend ){
            $dataArray[$key]['subCategoryName'] = $budgetVsSpend['subCategoryName'];
            $dataArray[$key]['budgetAmount'] = $budgetVsSpend['budgetAmountForSubCategory'];
            $dataArray[$key]['spendAmount'] = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getBudgetVsSpendsForPoiTotal($data, $budgetVsSpend['subCategoryID']);
        }

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Sub Category',
            'C9'=>'Budget',
            'D9'=>'Spend',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'BudgetVSSpend'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C2", 'Budget VS Spend');
        if(!empty($dataArray)){
            $index = 10;
            $count = 1;

            $budget = $dataArray['subCategoryName'];
            $budgetAmount = $dataArray['budgetAmount'];
            $spendAmount = $dataArray['spendAmount'] ? $dataArray['spendAmount'] : 0 ;;

            $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $budget);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $budgetAmount);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $spendAmount);

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

    public function subcategorySpendDetailsAction($month, $subCategoryID)
    {
        $projects = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->allProject();
        $budgets = $this->getDoctrine()->getRepository('PmsBudgetBundle:BudgetForSubCategory')->getBudgetVsSpendSubCategoryWiseSingle($subCategoryID, $month);
        $spends = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getBudgetVsSpendsForPoiTotalSingle($month, $subCategoryID);

        return $this->render('PmsReportBundle:Report:projectSpendSubCategoryWiseSingle.html.twig', array(
            'budgets' => $budgets,
            'spends' => $spends,
            'projects' => $projects,
            'month' =>$month,
            'subCategoryId' =>$subCategoryID
        ));
    }

    public function projectWiseSpendReportAction(Project $project,$month,$subcategory){
        
        $spends = $this->getDoctrine()
                       ->getRepository('PmsCoreBundle:PurchaseOrderItem')
                       ->getProjectWiseItemSpend($month, $project,$subcategory);

        return $this->render('PmsReportBundle:Report:projectWiseSpendReport.html.twig', array(
            'spends' => $spends,
            'project' => $project,
            'month' =>$month,
            'subCategoryId' =>$subcategory
        ));
    }
    public function projectWiseSpendReportExcelAction(Project $project,$month,$subcategory)
    {

        $spends = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrderItem')
            ->getProjectWiseItemSpend($month, $project,$subcategory);
    //    var_dump($spends);die;
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Item',
            'C9'=>'Po Number',
            'D9'=>'Created By',
            'E9'=>'PR number',
            'F9'=>'Brand name',
            'G9'=>'vendor/buyer',
            'H9'=>'Po Date',
            'I9'=>'Unit',
            'J9'=>'Unit Price',
            'K9'=>'Quantity',
            'L9'=>'Sub total',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'BudgetVSSpend'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C2", 'Project Wise Spend Item Report');
        if(!empty($spends)){
            $index = 10;
            $count = 1;
            foreach($spends as $spend){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $spend['itemName']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $spend['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $spend['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $spend['prId']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $spend['brand_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $spend['vendor_name'] ? $spend['vendor_name'] : $spend['buyer_name'] );
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $spend['po_created_date']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $spend['itemUnit']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $spend['item_price']);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $spend['quantity']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $spend['subTotal']);

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
}