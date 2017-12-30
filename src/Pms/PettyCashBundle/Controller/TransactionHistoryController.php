<?php

namespace Pms\PettyCashBundle\Controller;

use Pms\CoreBundle\Controller\BaseController;
use Pms\PettyCashBundle\Entity\Bank;
use Pms\PettyCashBundle\Entity\Transaction;
use Pms\PettyCashBundle\Entity\TransactionHistory;
use Pms\PettyCashBundle\Form\BankType;
use Pms\PettyCashBundle\Form\TransactionHistoryType;
use Pms\PettyCashBundle\Form\TransactionType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class TransactionHistoryController extends BaseController
{
    public function indexAction()
    {
            $bankLists = $this->getDoctrine()->getRepository('PmsPettyCashBundle:Bank')->getAll();
             $totalBalance = $this->bankTotalBalanceCalculation();
        return $this->render('PmsPettyCashBundle:Cash:bankList.html.twig',array(
                'bankLists' => $bankLists,
                'totalBalance' =>$totalBalance
            ));
    }
    public function transactionHistoryAddAction(Request $request ,Transaction $transaction)
    {

         $transactionHistory = new TransactionHistory();
         $form = $this->createForm(new TransactionHistoryType(), $transactionHistory);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $transactionHistory->setCreatedDate(new \DateTime(date('Y-m-d')));
                $transactionHistory->setCreatedBy($this->getUser());
                $transactionHistory->setStatus('created');
                $transactionHistory->upload();
                $transactionHistory->setTransaction($transaction);
                $transactionHistory->uploadTwo();
                $transactionHistory->uploadThree();

                $this->getDoctrine()->getRepository("PmsPettyCashBundle:TransactionHistory")->create($transactionHistory);
                $massage = 'Transaction History Successfully Inserted';
                $this->successMessage($massage);
                return $this->redirect($this->generateUrl('transaction_history'));

            }
        }

        return $this->render('PmsPettyCashBundle:Transaction:transactionHistoryAddForm.html.twig', array(
                'form' => $form->createView()
            ));

    }
}
