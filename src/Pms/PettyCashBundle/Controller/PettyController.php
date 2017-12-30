<?php

namespace Pms\PettyCashBundle\Controller;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\CoreBundle\Controller\BaseController;
use Pms\PettyCashBundle\Entity\Bank;
use Pms\PettyCashBundle\Entity\Transaction;
use Pms\PettyCashBundle\Form\BankType;
use Pms\PettyCashBundle\Form\PettyCashBankReportSearchType;
use Pms\PettyCashBundle\Form\PettyCashReportSearchType;
use Pms\PettyCashBundle\Form\TransactionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;

class PettyController extends BaseController
{
    public function indexAction(Request $request)
    {
        list($form, $data) = $this->bankSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
            $bankLists = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->getBankLists($data);

             $totalBalance = $this->bankTotalBalanceCalculation();
        return $this->render('PmsPettyCashBundle:Cash:bankList.html.twig',array(
                'form' => $form->createView(),
                'bankLists' => $bankLists,
                'totalBalance' =>$totalBalance
            ));
    }

    public function bankSearchForm($request)
    {
        $form = new PettyCashBankReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }
    public function transactionHistoryAction(Request $request)
    {
        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $transactionLists = $this->getTransactionList($data);
//        var_dump($transactionLists);die;
        $transactionLists = $this->paginate($transactionLists);
        return $this->render('PmsPettyCashBundle:Transaction:transactionList.html.twig',array(
            'form'=>$form->createView(),
            'transactionLists' =>$transactionLists
            ));
    }
    public function paymentCompanyReportSearchForm($request)
    {
        $form = new PettyCashReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }
    public function reportHistoryAction()
    {
        list($totalAsCashAmount, $totalAsInvoiceAmount, $totalBankRemainingAmount) = $this->totalRemainningBalance();

        return $this->render('PmsPettyCashBundle:Report:reportList.html.twig',array(
               'totalBankAmount' => $totalBankRemainingAmount,
               'totalCashAmounts' => $totalAsCashAmount,
               'totalInvoiceAmounts' => $totalAsInvoiceAmount
            ));
    }

    public function attachmentViewAction(Request $request, Transaction $transaction, $index)
    {
        if(null !== $response = $this->checkAttachFileByIndex($transaction, $index)) {
            return $response;
        }

        switch($index) {
            case 1: return $this->render('PmsPettyCashBundle:Transaction:viewer.html.twig', array(
                'transactionView' => $transaction,
                'path' => $transaction->getPath(),
            ));
            case 2: return $this->render('PmsPettyCashBundle:Transaction:viewer.html.twig', array(
                'transactionView' => $transaction,
                'path' => $transaction->getPathTwo(),
            ));
            case 3: return $this->render('PmsPettyCashBundle:Transaction:viewer.html.twig', array(
                'transactionView' => $transaction,
                'path' => $transaction->getPathThree(),
            ));
        }
    }


    private function checkAttachFileByIndex(Transaction $transaction, $index)
    {
        if (null == $fileName = $transaction->getAbsolutePathByIndex($index)) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function bankAddAction(Request $request)
    {
        $bank = new Bank();

        $form = $this->createForm(new BankType(), $bank);
        $bank->setCreatedDate(new \DateTime(date('Y-m-d')));
        $bankUserId = $this->getUser()->getId();
        $bank->setCreatedBy($this->getDoctrine()->getRepository('UserBundle:User')->find($bankUserId));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getRepository("PmsPettyCashBundle:Bank")->create($bank);
                $massage = 'Bank Amount Successfully Inserted';
                $this->successMessage($massage);
                return $this->redirect($this->generateUrl('bank_amount'));

            }
        }

        return $this->render('PmsPettyCashBundle:Cash:bankAddForm.html.twig', array(
                'form' => $form->createView()
            ));

    }
    public function transactionAddAction(Request $request)
    {
        $transaction = new Transaction();
//        list($totalAsCashAmount, $totalAsInvoiceAmount, $totalBankRemainingAmount) = $this->totalRemainningBalance();
        $form = $this->createForm(new TransactionType(), $transaction);

         $transaction->setCreatedDate(new \DateTime(date('Y-m-d')));
        $transaction->setCreatedBy($this->getUser());
        $transaction->setStatus('created');
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $transaction->upload();
                $transaction->uploadTwo();
                $transaction->uploadThree();
                $this->getDoctrine()->getRepository("PmsPettyCashBundle:Transaction")->create($transaction);
                $massage = 'Transaction Amount Successfully Inserted';
                $this->successMessage($massage);
                return $this->redirect($this->generateUrl('transaction_history'));

            }
        }

        return $this->render('PmsPettyCashBundle:Transaction:transactionAddForm.html.twig', array(
                'form' => $form->createView()
            ));

    }
    public function transactionUpdateAction(Request $request, Transaction $entity) {

        list($totalAsCashAmount, $totalAsInvoiceAmount, $totalBankRemainingAmount) = $this->totalRemainningBalance();
        $form = $this->createForm(new TransactionType($totalBankRemainingAmount),$entity);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $entity->upload();
                $entity->uploadTwo();
                $entity->uploadThree();
                $this->getDoctrine()->getRepository("PmsPettyCashBundle:Transaction")->update($entity);
                $massage = 'Transaction Amount Successfully Updated';
                $this->successMessage($massage);
                return $this->redirect($this->generateUrl('transaction_history'));

            }
        }
        return $this->render('PmsPettyCashBundle:Transaction:transactionAddForm.html.twig', array(
                'entity' =>$entity,
                'form' => $form->createView()
            ));
    }
    public function bankDeleteAction(Bank $entity) {

        $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->delete($entity);
        $massage = 'Bank Successfully Deleted';
        $this->successMessage($massage);
        return $this->redirect($this->generateUrl('bank_amount'));
    }
    public function transactionDeleteAction(Transaction $entity) {

        $this->getDoctrine()->getRepository('PmsPettyCashBundle:Transaction')->delete($entity);
        $massage = 'Transaction Successfully Deleted';
        $this->successMessage($massage);
        return $this->redirect($this->generateUrl('transaction_history'));
    }
    public function transactionStatusUpdateAction(Transaction $entity) {
        $entity->setStatus('acknowledged');
        $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->update($entity);
        $massage = 'Transaction Successfully acknowledged';
        $this->successMessage($massage);
        return $this->redirect($this->generateUrl('transaction_history'));
    }

    /**
     * @return mixed
     */
    public function getTransactionList($data)
    {

        $user = $this->getUser();

        if (!$user->hasRole("ROLE_SUPER_ADMIN")) {

        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        } else {

            $userId = false;
        }
        $transactionLists = $this->getDoctrine()->getRepository(
            'PmsPettyCashBundle:Transaction'
        )->getAllByCreatedBy($userId,$data);

       /* if ($user->hasRole("ROLE_SUPER_ADMIN")) {
            $transactionLists = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Transaction')->getAll();
            return $transactionLists;
        } else {
            $userId = $this->get('security.context')->getToken()->getUser()->getId();
            $transactionLists = $this->getDoctrine()->getRepository(
                'PmsPettyCashBundle:Transaction'
            )->getAllByCreatedBy($userId,$start_date,$end_date);
            return $transactionLists;
        }*/
        return $transactionLists;
    }

    /**
     * @param $totalAddedBalance
     * @param $totalSubtractBalance
     */
    public function totalBackBalanceCalculation($totalAddedBalance, $totalSubtractBalance)
    {

        $totalBalance = $totalAddedBalance[0]['TotalAddedBalance'] - $totalSubtractBalance[0]['TotalSubtractBalance'];

        return $totalBalance;
    }


    /**
     * @return mixed
     */
    public function bankTotalBalanceCalculation()
    {
        $totalAddedBalance    = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->getTotalAddedBalance();
        $totalSubtractBalance = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->getTotalSubtractBalance(
        );
        $totalBalance         = $this->totalBackBalanceCalculation($totalAddedBalance, $totalSubtractBalance);
        return $totalBalance;
    }

    /**
     * @return array
     */
    private function totalRemainningBalance()
    {
        $totalAsCashAmount = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Transaction')->getTotalCashAmount();
        
        $totalAsInvoiceAmount = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Transaction')->getTotalInvoiceAmount();
        
        $totalBalance = $this->bankTotalBalanceCalculation();
        
        $totalInvoiceAmount = 0;
        foreach ($totalAsInvoiceAmount as $invoiceAmount) {
            $totalInvoiceAmount += $invoiceAmount['TotalInvoiceAmount'];
        }
        $totalCashAmount = 0;

        foreach ($totalAsCashAmount as $total) {
            $totalCashAmount += $total['TotalCashAmount'];
        }

        $totalBankRemainingAmount = $totalBalance - ($totalCashAmount + $totalInvoiceAmount);

        return array($totalAsCashAmount, $totalAsInvoiceAmount, $totalBankRemainingAmount);
    }

    public function pettyCashBankExcelReportAction(Request $request)
    {
        $item       = $request->request->all();
        list($form,$data) = $this->bankSearchForm($request);

        $bankLists = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->getBankLists($data);
        $totalBalance = $this->bankTotalBalanceCalculation();

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Date',
            'C9'=>'Amount',
            'D9'=>'Created By ',
            'E9'=>'Status',
            'F9'=>'notes',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PettyCashBankReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("B5", 'Total Bank Balance');
        $objPHPExcel->getActiveSheet()->setCellValue("C5", $totalBalance);
        $objPHPExcel->getActiveSheet()->mergeCells('C3:D3')->setCellValue('C3','Petty Cash Bank Report');



        if(!empty($bankLists)){
            $index = 11;
            $count = 1;
            foreach($bankLists as $bankList){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $bankList['createdDate'] ? $bankList['createdDate']->format('Y-m-d'):' ');
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $bankList['amount'] ? $bankList['amount'] :'' );
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $bankList['username']?$bankList['username']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $bankList['status']?$bankList['status']:'');
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $bankList['notes']?$bankList['notes']:'');
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

    public function pettyTransactionExcelAction(Request $request)
    {
        list($form, $data) = $this->paymentCompanyReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $transactionLists = $this->getTransactionList($data);
//var_dump($transactionLists);die;
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Transaction Id',
            'C9'=>'Start Date',
            'D9'=>'Given Amount',
            'E9'=>'AS',
            'F9'=>'From',
            'G9'=>'To',
            'H9'=>'Due Amount',
            'I9'=>'Status',
            'J9'=>'Close Date',
            'K9'=>'Adjustment Date',
            'L9'=>'Adjustment Amount',
            'M9'=>'As',
            'N9'=>'From',
            'O9'=>'To',

        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'Transaction'.'.xlsx';


        if(!empty($transactionLists)){
            $index = 11;
            $count = 1;
            foreach($transactionLists as $transactionList){
                $totalTransactionHistory = 0;
                $closeDate = '';
                foreach ($transactionList->getTransactionHistory() as $transactionHistory) {

                    $totalTransactionHistory +=$transactionHistory->getTransactionHistoryAmount();

                    $closeDate = $transactionHistory->getCreatedDate()->format('Y-m-d');
                }

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $transactionList->getId());
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $transactionList->getCreatedDate() ? $transactionList->getCreatedDate()->format('Y-m-d'):' ');
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $transactionList->getTransactionAmount());
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $transactionList->getTransactionType());
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $transactionList->getCreatedBy()->getUsername());
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $transactionList->getTransactTo()->getUsername());
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, ($transactionList->getTransactionAmount() - $totalTransactionHistory));
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index, $transactionList->getStatus());
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $closeDate);
                // $index += 1;
                foreach ($transactionList->getTransactionHistory() as $id => $transactionHistory){


                    $objPHPExcel->getActiveSheet()->setCellValue("K".$index, $transactionHistory->getCreatedDate() ? $transactionHistory->getCreatedDate()->format('Y-m-d'):' ');
                    $objPHPExcel->getActiveSheet()->setCellValue("L".$index, $transactionHistory->getTransactionHistoryAmount() ? $transactionHistory->getTransactionHistoryAmount():' ');
                    $objPHPExcel->getActiveSheet()->setCellValue("M".$index, $transactionHistory->getTransactionType() ? $transactionHistory->getTransactionType():' ');
                    $objPHPExcel->getActiveSheet()->setCellValue("N".$index, $transactionHistory->getCreatedBy() ? $transactionHistory->getCreatedBy()->getUsername():' ');
                    $objPHPExcel->getActiveSheet()->setCellValue("O".$index, $transactionList->getCreatedBy() ? $transactionList->getCreatedBy()->getUsername():' ');
                    $index += 1;
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

}
