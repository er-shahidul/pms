<?php

namespace Pms\BudgetBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\BudgetBundle\Form\SearchForm\DateSearchType;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\SystemUsagesSummaryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class BudgetReportController extends Controller
{
    public function budgetReportSearchForm($request)
    {
        $form = new DateSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function budgetReportYearlySearchForm($request)
    {
        $form = new SystemUsagesSummaryType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function budgetReportAction(Request $request)
    {
        list($form, $data) = $this->budgetReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $budgets = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
                        ->getBudgetReport($data);

        $budgetsTotal = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
                             ->getBudgetTotalReport($data);

        return $this->render('PmsBudgetBundle:Report:budgetReport.html.twig', array(
            'budgets' => $budgets,
            'budgetsTotal' => $budgetsTotal,
            'form' => $form->createView(),
        ));
    }

    public function budgetReportYearlyAction(Request $request)
    {
        list($form, $data) = $this->budgetReportYearlySearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($totalMonthlyBudget, $totalMonthlyBudgetSpend, $totalSavingsOrSpendOverBudget, $month) = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
            ->getBudgetReportYearly($data);

        return $this->render('PmsBudgetBundle:Report:budgetReportYearly.html.twig', array(
            'totalMonthlyBudget' => $totalMonthlyBudget,
            'totalSavingsOrSpendOverBudget' => $totalSavingsOrSpendOverBudget,
            'month' =>$month,
            'totalMonthlyBudgetSpend' =>$totalMonthlyBudgetSpend,
            'form' => $form->createView(),
        ));
    }

    public function budgetReportExcelAction(Request $request)
    {
        list($form, $data) = $this->budgetReportSearchForm($request);

        $budgets = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
                        ->getBudgetReport($data);

        $budgetsTotal = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
                             ->getBudgetTotalReport($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Month',
            'C9'=>'Project',
            'D9'=>'CreatedBy',
            'E9'=>'CreatedDate',
            'F9'=>'ApprovedBy',
            'G9'=>'ApprovedDate',
            'H9'=>'Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'BudgetReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Budget Report');
        if(!empty($budgets)){
            $index = 10;
            $count = 1;

            foreach($budgets as $budget){

                $month = $budget->getMonth() ? $budget->getMonth()->format('M-y') : "";
                $project = $budget->getProject() ? $budget->getProject()->getProjectName() : "";
                $createdBy = $budget->getCreatedBy() ? $budget->getCreatedBy()->getUsername() : "";
                $createdDate = $budget->getCreatedDate() ? $budget->getCreatedDate()->format('d-m-Y') : "";
                $approvedThree = $budget->getApprovedThree() ? $budget->getApprovedThree()->getUsername() : "";
                $approvedThreeDate = $budget->getApprovedThreeDate() ? $budget->getApprovedThreeDate()->format('d-m-Y') : "";
                $amount = $budget->getNetTotal() ? $budget->getNetTotal() : "";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $month);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $project);

                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $createdBy);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $createdDate);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $approvedThree);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $approvedThreeDate);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $amount);

                $index++;
                $count++;
            }

            $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $budgetsTotal[0]['totalBudget']);

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

    public function budgetReportYearlyExcelAction(Request $request)
    {
        list($form, $data) = $this->budgetReportYearlySearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($totalMonthlyBudget, $totalMonthlyBudgetSpend, $totalSavingsOrSpendOverBudget, $month) = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
            ->getBudgetReportYearly($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'Month',
            'B9'=>'Total Budget',
            'C9'=>'Total Spend',
            'D9'=>'Total Savings Or Spend over Budget',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'BudgetReportYearly'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Budget Report');
        if(!empty($totalMonthlyBudget)){
            $index = 10;
            $count = 0;

            foreach($totalMonthlyBudget as $totalMonthly){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $month[$count]);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $totalMonthlyBudget[$count]['totalBudget']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $totalMonthlyBudgetSpend[$count]['totalBudgetSpend']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $totalSavingsOrSpendOverBudget[$count]);

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