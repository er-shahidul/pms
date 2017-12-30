<?php

namespace Pms\CoreBundle\Controller;

use DateTime;
use Doctrine\ORM\Repository;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Settings;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\CoreBundle\Entity\Payment;
use Pms\CoreBundle\Entity\ReadyForPayment;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Pms\CoreBundle\Entity\Receive;
use Pms\CoreBundle\Form\PaymentType;
use Pms\CoreBundle\Form\SearchForm\PaymentVerifiedSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receive controller.
 *
 */
class PaymentController extends Controller
{
    public function indexAction(Request $request, $status = 'payment-request')
    {
        $keyword = $this->get('request')->query->get('sort');
        $value = $this->get('request')->query->get('direction');

        list($form, $data) = $this->paymentVerifiedSearch($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $readyForPaymentRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment');
        $payments = $this->paginate($readyForPaymentRepository->getReadyForPaymentSearch($data,$this->getUser(),$keyword,$value));

        return $this->render('PmsCoreBundle:Payment:home.html.twig', array(
            'payments' => $payments,
            'status' => $status,
            'form'=>$form->createView()
        ));
    }

    public function advancePaymentListAction(Request $request)
    {
        $keyword = $this->get('request')->query->get('sort');
        $value = $this->get('request')->query->get('direction');

        list($form, $data) = $this->paymentVerifiedSearch($request);
        $form = $this->createForm($form);
        $form->submit($data);

        $readyForPaymentRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment');
        $advancePayments = $this->paginate($readyForPaymentRepository
                                             ->getAdvancePaymentLists($data,$keyword, $value, $this->getUser()));

        return $this->render('PmsCoreBundle:Payment:homePaymentAdvance.html.twig', array(
            'payments' => $advancePayments,
            'status'=>'',
            'form'=>$form->createView()

        ));
    }

    public function advancePaymentArchiveListAction(Request $request,$status = 'archive')
    {
        $keyword = $this->get('request')->query->get('sort');
        $value = $this->get('request')->query->get('direction');

        list($form, $data) = $this->paymentVerifiedSearch($request);
        $form = $this->createForm($form);
        $form->submit($data);

        $readyForPaymentRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment');
        $advancePayments = $this->paginate($readyForPaymentRepository->getAdvancePaymentArchiveLists($data,$keyword, $value, $this->getUser()));

        return $this->render('PmsCoreBundle:Payment:homePaymentAdvance.html.twig', array(
            'payments' => $advancePayments,
            'status'=>$status,
            'form'=>$form->createView()
        ));
    }

    public function approvedPaymentListAction(Request $request)
    {
        $keyword = $this->get('request')->query->get('sort');
        $value = $this->get('request')->query->get('direction');

        list($form, $data) = $this->paymentVerifiedSearch($request);
        $form = $this->createForm($form);
        $form->submit($data);

        $paymentsRepository =  $this->getDoctrine()->getRepository('PmsCoreBundle:Payment');
        $payments = $this->paginate($paymentsRepository->findVerifiedList($data,$keyword, $value, $this->getUser()));

        return $this->render('PmsCoreBundle:Payment:verifiedList.html.twig', array(
            'payments' => $payments,
            'form' => $form->createView(),
        ));
    }

    public function paymentVerifiedSearch($request)
    {
        $form = new PaymentVerifiedSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function approvedAdvancePaymentAction(Request $request,ReadyForPayment $readyForPayment)
    {
        $id = $readyForPayment->getId();
        $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                           ->advancePaymentVerified($id,$this->getUser());

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Advance Payment Verified Successfully'
        );

        return $this->redirect($this->generateUrl('payment_advance'));
    }

    public function verifiedAction(Request $request)
    {

        $paymentList = $request->request->get('list');

        $purchaseOrder  = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->find($paymentList[0]);


        $data = '';
        $totalRequestAmount = 0;
        foreach($paymentList as $row){

            $rfp  = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->find($row);

           // $totalRequestAmount +=$rfp->getPurchaseOrder()->getAdvancePaymentAmount();
            $totalRequestAmount +=$rfp->getAmount();
            $grn = $rfp->getGrn() ? $rfp->getGrn()->getId():'Adv';
            $verifiedBy = $rfp->getVerifiedBy() ? $rfp->getVerifiedBy()->getUsername():'';
            $data .= '<input type="hidden" name="readyForPaymentId[]" value ="'.$rfp->getId().'"/>';
            $data .= '<input type="hidden" name="grnId[]" value ="'.$grn.'"/>';
            $data .= '<tr>';
            $data .='<td>'.$rfp->getId().'</td>';
            $data .='<td>'.$verifiedBy.'</td>';
            $data .='<td>'.$rfp->getBillDate()->format('d-m-y').'/'.$rfp->getBillNumber().'</td>';
            if($grn !='Adv'){
                $data .=' <td><a class="btn" style="text-decoration:underline" target="_blank" href ="/receive-grn/details/'.$grn.'" >
                                                        '.$grn.'</a></td>';
            } else{
                $data .='<td>'.$grn.'</td>';
            }

            $data .='<td>'.$rfp->getAmount().'</td>';
//            $data .='<td>'.$rfp->getPurchaseOrder()->getAdvancePaymentAmount().'</td>';
            $data .='<td>'.$rfp->getPurchaseOrder()->getPaymentType().'</td>';
            if($grn !='Adv'){
                if(($rfp->getGrn()->getInvoice())){
                /*$data .='<td><a class="btn" href="/document/attachment-view/'.$rfp->getGrn()->getInvoice()->getId().'/1"
                                                            <i class="fa fa-cloud-upload"></i></a></td>';*/

                    $data .= '<td><a class="btn" target="_blank" href="/document/attachment-view/'.$rfp->getGrn()->getInvoice()->getId().'/1">
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                }else {
                    $data .='<td>&nbsp;</td>';
                }
                if($rfp->getGrn()->getCalan())
                {
                    $data .= '<td><a class="btn" target="_blank" href="/document/attachment-view/'.$rfp->getGrn()->getCalan()->getId().'/1">
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                } else {
                    $data .=' <td>&nbsp;</td>';
                }
                if($rfp->getGrn())
                {
                    $data .= '<td><a class="btn" target="_blank" href = "/receive/attachment-view/'.$rfp->getGrn()->getId().'/1" >
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                } else {
                    $data .=' <td>&nbsp;</td>';
                }
                if($purchaseOrder->getPurchaseOrder()->getPath() != null){
                    $data .=' <td><a class="btn" target="_blank" href ="/purchase-order/attachment-view/'.$purchaseOrder->getPurchaseOrder()->getId().'/1" >
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                } else {
                    $data .='<td>&nbsp;</td>';
                }
            } else{
                $data .= '<td></td>';
                $data .= '<td></td>';
                $data .= '<td></td>';
                $data .= '<td></td>';
            }
            $data .=' <td><a class="btn" style="text-decoration:underline" target="_blank" href ="/purchase-order/details-others/'.$purchaseOrder->getPurchaseOrder()->getId().'" >
                                                        '.$purchaseOrder->getPurchaseOrder()->getId().'</a></td>';
            $data .='<tr/>';
        }

          $paymentHistory  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
                                ->findBy(array('purchaseOrder'=>$purchaseOrder->getPurchaseOrder()->getId(),'status'=> 2 ));

        return $this->render('PmsCoreBundle:Payment:verified.html.twig', array(
            'payment' => $purchaseOrder,
            'total_request_amount' =>$totalRequestAmount,
            'paymentHistory' => $paymentHistory,
            'readyForPayment'=>$data
        ));
    }
    public function verifiedSendBackAction(ReadyForPayment $id)
    {

       // $paymentList = $request->request->get('list');
            $paymentList = array($id);
//        var_dump($paymentList);die;
        $purchaseOrder  = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->find($paymentList[0]);


        $data = '';
        $totalRequestAmount = 0;
        foreach($paymentList as $row){

            $rfp  = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->find($row);

            $totalRequestAmount +=$rfp->getAmount();
            $grn = $rfp->getGrn() ? $rfp->getGrn()->getId():'Adv';
            $verifiedBy = $rfp->getVerifiedBy() ? $rfp->getVerifiedBy()->getUsername():'';
            $data .= '<input type="hidden" name="readyForPaymentId[]" value ="'.$rfp->getId().'"/>';
            $data .= '<input type="hidden" name="grnId[]" value ="'.$grn.'"/>';
            $data .= '<tr>';
            $data .='<td>'.$rfp->getId().'</td>';
            $data .='<td>'.$verifiedBy.'</td>';
            $data .='<td>'.$rfp->getBillDate()->format('d-m-y').'/'.$rfp->getBillNumber().'</td>';
            if($grn !='Adv'){
                $data .=' <td><a class="btn" style="text-decoration:underline" target="_blank" href ="/receive-grn/details/'.$grn.'" >
                                                        '.$grn.'</a></td>';
            } else{
                $data .='<td>'.$grn.'</td>';
            }

            $data .='<td>'.$rfp->getAmount().'</td>';
            $data .='<td>'.$rfp->getPurchaseOrder()->getPaymentType().'</td>';
            if($grn !='Adv'){
                if(($rfp->getGrn()->getInvoice())){
                    /*$data .='<td><a class="btn" href="/document/attachment-view/'.$rfp->getGrn()->getInvoice()->getId().'/1"
                                                                <i class="fa fa-cloud-upload"></i></a></td>';*/

                    $data .= '<td><a class="btn" target="_blank" href="/document/attachment-view/'.$rfp->getGrn()->getInvoice()->getId().'/1">
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                }else {
                    $data .='<td>&nbsp;</td>';
                }
                if($rfp->getGrn()->getCalan())
                {
                    $data .= '<td><a class="btn" target="_blank" href="/document/attachment-view/'.$rfp->getGrn()->getCalan()->getId().'/1">
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                } else {
                    $data .=' <td>&nbsp;</td>';
                }
                if($rfp->getGrn())
                {
                    $data .= '<td><a class="btn" target="_blank" href = "/receive/attachment-view/'.$rfp->getGrn()->getId().'/1" >
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                } else {
                    $data .=' <td>&nbsp;</td>';
                }
                if($purchaseOrder->getPurchaseOrder()->getPath() != null){
                    $data .=' <td><a class="btn" target="_blank" href ="/purchase-order/attachment-view/'.$purchaseOrder->getPurchaseOrder()->getId().'/1" >
                                                            <i class="fa fa-cloud-upload"></i></a></td>';
                } else {
                    $data .='<td>&nbsp;</td>';
                }
            } else{
                $data .= '<td></td>';
                $data .= '<td></td>';
                $data .= '<td></td>';
                $data .= '<td></td>';
            }
            $data .=' <td><a class="btn" style="text-decoration:underline" target="_blank" href ="/purchase-order/details-others/'.$purchaseOrder->getPurchaseOrder()->getId().'" >
                                                        '.$purchaseOrder->getPurchaseOrder()->getId().'</a></td>';
            $data .='<tr/>';
        }

        $paymentHistory  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
            ->findBy(array('purchaseOrder'=>$purchaseOrder->getPurchaseOrder()->getId(),'status'=> 2 ));

        return $this->render('PmsCoreBundle:Payment:verifiedSendBack.html.twig', array(
            'payment' => $purchaseOrder,
            'total_request_amount' =>$totalRequestAmount,
            'paymentHistory' => $paymentHistory,
            'readyForPayment'=>$data
        ));
    }

    public function paymentCompanyMonthlyDetailAction(Request $request,$date, $monthValue)
    {
        $companyType = $request->get('companyType');
        $start_date =  date('Y-'.$monthValue.'-d',strtotime($date));
        $end_Date = date('Y-'.$monthValue.'-t',strtotime($date));
   
        $dateObj   = DateTime::createFromFormat('!m', $monthValue);
        $monthName = $dateObj->format('F');

        $paymentCompanyMonthlyDetailsPo  = $this->getDoctrine()
                                                    ->getRepository('PmsCoreBundle:PurchaseOrder')
                                                    ->getPaymentCompanyMonthlyDetail($start_date,$end_Date,$companyType);


        $paymentCompanyMonthlyDetailsReceive = $this->getDoctrine()
                                                    ->getRepository('PmsCoreBundle:Receive')
                                                    ->getPaymentCompanyMonthlyDetailReceive($start_date,$end_Date,$companyType);

                            $readyForPayment = $this->getDoctrine()
                                                    ->getRepository('PmsCoreBundle:ReadyForPayment')
                                                    ->getPaymentCompanyMonthlyDetail($start_date,$end_Date,$companyType);

                                    $payment = $this->getDoctrine()
                                                    ->getRepository('PmsCoreBundle:Payment')
                                                    ->getPaymentCompanyMonthlyDetail($start_date,$end_Date,$companyType);


         return $this->render('PmsCoreBundle:Payment:paymentCompanyMonthlyDetail.html.twig', array(
            'paymentCompanyMonthlyDetails' => $paymentCompanyMonthlyDetailsPo,
            'paymentCompanyMonthlyDetailsReceive' => $paymentCompanyMonthlyDetailsReceive,
            'readyForPayment' => $readyForPayment,
            'payment' => $payment,
             'month'=>$monthName,
             'dateValue'=>$date,
             'monthValue'=>$monthValue,
             'companyType'=>$companyType,
        ));
    }

    public function routerParams()
    {
        $router = $this->container->get('router');
        $request = $this->container->get('request');

        $routeName = $request->attributes->get('_route');
        $routeParams = $request->query->all();
        foreach ($router->getRouteCollection()->get($routeName)->compile()->getVariables() as $variable) {
            $routeParams[$variable] = $request->attributes->get($variable);
        }

        return $routeParams;
    }
    /*public function paymentCompanyMonthlyDetailAction($date, $monthValue)
    {

        $start_date =  date('Y-'.$monthValue.'-d',strtotime($date));
        $end_Date = date('Y-'.$monthValue.'-t',strtotime($date));

        $paymentCompanyMonthlyDetails = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->getPaymentCompanyMonthlyDetail($start_date,$end_Date);
        $readyForPayment = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->getPaymentCompanyMonthlyDetail($start_date,$end_Date);
        $payment = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->getPaymentCompanyMonthlyDetail($start_date,$end_Date);

         return $this->render('PmsCoreBundle:Payment:paymentCompanyMonthlyDetail.html.twig', array(
            'paymentCompanyMonthlyDetails' => $paymentCompanyMonthlyDetails,
            'readyForPayment' => $readyForPayment,
            'payment' => $payment,
        ));
    }*/



    public function paymentApprovedAction(Request $request)
    {

        $paymentData = $request->request->all();

       if(isset($paymentData['send-back'])){
           $this->sendBackPaymentRequestToReceivedItem($paymentData);
           $this->receiveItemSendBackFromPaymentRequest($paymentData);
           return $this->redirect($this->generateUrl('approved_payment'));
       } else {

           $po = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($paymentData['purchaseOrderId']);

           if($po->getAdvancePaymentAmount() > 0){
               $po->setIsAdvancePayment(1);
               $this->getDoctrine()->getManager()->persist($po);
               $this->getDoctrine()->getManager()->flush();
           }

           $payment = $this->getDoctrine()
                           ->getRepository('PmsCoreBundle:Payment')
                           ->insert($paymentData,$po,$this->getUser());
           $this->getDoctrine()
                ->getRepository('PmsCoreBundle:ReadyForPayment')
                ->updatePayment($paymentData['readyForPaymentId'],$payment);

           return $this->redirect($this->generateUrl('approved_payment'));
       }

    }

    public function sendBackPaymentRequestToReceivedItem($paymentData){

        $this->getDoctrine()
            ->getRepository('PmsCoreBundle:ReadyForPayment')
            ->sendBackReadyForPayment($paymentData);

    }
    public function receiveItemSendBackFromPaymentRequest($paymentData){

//        var_dump($paymentData);die;
        $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Receive')
            ->sendBackReceiveItem($paymentData,$this->getUser());
    }

    public function paymentAdvanceVerifiedAction(Request $request,ReadyForPayment $readyForPayment)
    {
        $payment  = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                                        ->find($readyForPayment->getId());

        return $this->render('PmsCoreBundle:Payment:advanceVerified.html.twig', array(
            'payment' => $payment
        ));
    }

    public function createAction($id)
    {
        $payment  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->find($id);
        $poID = $payment->getPurchaseOrder()->getId();
        $paymentHistory  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
                                ->findBy(array('purchaseOrder'=>$poID,'status'=> 2 ));

        return $this->render('PmsCoreBundle:Payment:verified.html.twig', array(
            'payment' => $payment,
            'paymentHistory' => $paymentHistory,
        ));
    }

    public function confirmAction($id)
    {

        $payment = new Payment();

        $form    = $this->createForm(new PaymentType(), $payment);

        $payment  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->find($id);

        $poID = $payment->getPurchaseOrder()->getId();
        $paymentHistory  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
                                ->findBy(array('purchaseOrder'=>$poID,'status'=> 2 ));

        return $this->render('PmsCoreBundle:Payment:confirmPayment.html.twig', array(
            'payment' => $payment,
            'paymentHistory' => $paymentHistory,
             'form'=>$form->createView()
        ));
    }
    public function confirmVendorWiseAction(Request $request)
    {
       $payments = $request->request->get('list');

        $payment = new Payment();

        $form    = $this->createForm(new PaymentType(), $payment);

        $paymentList = array();
        $paymentHistories = array();
        foreach($payments as $key => $payment){

            $paymentList[]  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->find($payment);

            $poID = $paymentList[$key]->getPurchaseOrder()->getId();

            $paymentHistories[$key]  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
                ->findBy(array('purchaseOrder'=>$poID,'status'=> 2 ));
        }

        return $this->render('PmsCoreBundle:Payment:confirmPayment.html.twig', array(
            'paymentList' => $paymentList,
            'payment' => $payment,
            'paymentHistory' => $paymentHistories,
             'form'=>$form->createView()
        ));
    }

    public function confirmDetailsAction(Payment $payment)
    {
        $poID = $payment->getPurchaseOrder()->getId();

        $paymentHistory  = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
                                ->findBy(array('purchaseOrder'=>$poID,'status'=> 2 ));

        return $this->render('PmsCoreBundle:Payment:detailsConfirmPayment.html.twig', array(
            'payment' => $payment,
            'paymentHistory' => $paymentHistory,
        ));
    }

    public function paymentDeliveryAction(Request $request, Payment $payment)
    {
        $paymentData = $request->request->all();

        $payment->setCheque($paymentData['pms_corebundle_payment']['cheque']);
        $payment->setPaymentBy($this->getUser());
        $payment->setPaymentDate(new \DateTime());
        $payment->setRelatedBank($this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')
                ->find($paymentData['pms_corebundle_payment']['relatedBank']));
        $payment->setRequestAmount($paymentData['pms_corebundle_payment']['requestAmount']);
        $payment->setStatus(2);

        $this->getDoctrine()->getManager()->persist($payment);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('payment_archive'));
    }
    public function paymentDeliveryVendorWiseAction(Request $request)
    {
        $paymentData = $request->request->all();
        $paymentsIds = json_decode($paymentData['paymentId']);
        $bank = $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')
            ->find($paymentData['pms_corebundle_payment']['relatedBank']);

        foreach($paymentsIds as $key => $paymentId){

            $payment = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->find($paymentId);
            $paymentsRequestAmount = json_decode($paymentData['pms_corebundle_payment']['requestAmount']);
            $payment->setCheque($paymentData['pms_corebundle_payment']['cheque']);
            $payment->setPaymentBy($this->getUser());
            $payment->setPaymentDate(new \DateTime());
            $payment->setRelatedBank($bank);
            $payment->setRequestAmount($paymentsRequestAmount[$key]);
            $payment->setStatus(2);

        }

        $this->getDoctrine()->getManager()->persist($payment);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('payment_archive'));
    }

    public function paymentPurchaseOrderAutoCompleteAction(Request $request,$status='archive')
    {
        $poNo = $_REQUEST['q'];

        if ($poNo) {
            $poNoQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')
                ->poNoAutoComplete($poNo, $this->getUser(),$status);

        }

        return new JsonResponse($poNoQuery);
    }
    public function paymentRequestPurchaseOrderAutoCompleteAction(Request $request,$status='archive')
    {
        $poNo = $_REQUEST['q'];

        if ($poNo) {
            $poNoQuery = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                ->poNoForRequestPaymentAutoComplete($poNo, $this->getUser(),$status);

        }

        return new JsonResponse($poNoQuery);
    }

    public function paymentArchiveAction(Request $request)
    {
        $keyword = $this->get('request')->query->get('sort');
        $value = $this->get('request')->query->get('direction');

        list($form, $data) = $this->paymentVerifiedSearch($request);
        $form = $this->createForm($form);
        $form->submit($data);
        $paymentsRepository =  $this->getDoctrine()->getRepository('PmsCoreBundle:Payment');
        $payments = $this->paginate($paymentsRepository->getPaymentArchiveList($data, $keyword, $value));
 //       $payments = $paymentsRepository->getPaymentArchiveList($data, $keyword, $value);
        // var_dump($payments);die;

        return $this->render('PmsCoreBundle:Payment:archiveList.html.twig', array(
            'payments' => $payments,
            'form' => $form->createView(),
        ));
    }

    public function paymentDeleteAction(Payment $payment)
    {
        $id = $payment->getId();
        $ready =  $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                       ->findBy(array('payment'=>$id));
        $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')->updatePaymentId($ready);
        $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->delete($payment);

        return $this->redirect($this->generateUrl('payment'));
    }

    public function paymentAdvanceReceiveAction(Request $request)
    {
        $paymentReceiveData = $request->request->all();
        $billNumber = substr($paymentReceiveData['billNumber'], -4);
        $billDate = substr($paymentReceiveData['billNumber'], 0, 10);
        $billCheck = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
            ->findBy(array('vendor' => $paymentReceiveData['vendorId'],
                    'billNumber' => $billNumber) );

        if (!$billCheck) {

            if ($paymentReceiveData['paymentId']) {
                $bank = $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')
                                            ->find($paymentReceiveData['bank']);
                $updateReadyPayment = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment')
                                                          ->find($paymentReceiveData['paymentId']);
                $updateReadyPayment->setBillNumber($billNumber);
                $this->getDoctrine()->getManager()->persist($updateReadyPayment);
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Payment Successfully Updated'
                );

            } else {
                $readyForPayment = new ReadyForPayment();
                $bank = $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')->find($paymentReceiveData['bank']);
                $purchaseOrder = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')->find($paymentReceiveData['purchaseOrderId']);
                $readyForPayment->setPurchaseOrder($purchaseOrder);
                $readyForPayment->setBillDate(new \DateTime($billDate));
                $readyForPayment->setBillNumber($paymentReceiveData['billNumber']);
                $this->getDoctrine()->getManager()->persist($readyForPayment);
                $purchaseOrder->setAdvanceStatus(true);
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Payment Successfully Inserted'
                );
            }
            $this->getDoctrine()->getManager()->flush();

            return new Response();
        } else {
            $msg = 'your bill number already exist';

            return new  Response($msg);
        }
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
            50/*limit per page*/
        );

        return $value;
    }
    public function executeQueryForExcel($dql)
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
            50/*limit per page*/
        );

        return $value;
    }

    public function paymentMonthlyReportPoInfoExcelAction(Request $request)
    {

        $date = $request->request->get('dateValue');
        $monthValue = $request->request->get('monthValue');
        $companyType = $request->request->get('companyType');

        $start_date =  date('Y-'.$monthValue.'-d',strtotime($date));
        $end_Date = date('Y-'.$monthValue.'-t',strtotime($date));


        $paymentCompanyMonthlyDetailsPo  = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:PurchaseOrder')
            ->getPaymentCompanyMonthlyDetail($start_date,$end_Date,$companyType);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'PO Number',
            'C9'=>'PO Issued Date',
            'D9'=>'PO Issued Month',
            'E9'=>'Project Name',
            'F9'=>'PO Amount',
            'G9'=>'Vat Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'PaymentCompanyMonthlyPoInfoReport'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'monthly payment detail PO Report');

        if(!empty($paymentCompanyMonthlyDetailsPo[0])){
            $index = 11;
            $count = 1;

            foreach($paymentCompanyMonthlyDetailsPo[0] as $detail){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $detail['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $detail['poDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $detail['poDate']->format('F-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $detail['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $detail['poAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("g".$index, $detail['tax']);


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
    public function paymentMonthlyReportReceiveInfoExcelAction(Request $request)
    {

        $date = $request->request->get('dateValue');
        $monthValue = $request->request->get('monthValue');
        $companyType = $request->request->get('companyType');

        $start_date =  date('Y-'.$monthValue.'-d',strtotime($date));
        $end_Date = date('Y-'.$monthValue.'-t',strtotime($date));


        $paymentCompanyMonthlyDetailsReceive = $this->getDoctrine()
                                                    ->getRepository('PmsCoreBundle:Receive')
                                                    ->getPaymentCompanyMonthlyDetailReceive($start_date,$end_Date,$companyType);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'PO Number',
            'C9'=>'PO Issued Date',
            'D9'=>'PO Issued Month',
            'E9'=>'Project Name',
            'F9'=>'GRN Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'paymentCompanyMonthlyDetailsReceive'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'monthly payment detail PO Report');

        if(!empty($paymentCompanyMonthlyDetailsReceive[0])){
            $index = 11;
            $count = 1;

            foreach($paymentCompanyMonthlyDetailsReceive[0] as $detail){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $detail['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $detail['poDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $detail['poDate']->format('F-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $detail['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $detail['GrnAmount']);


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

    public function paymentMonthlyReportPaymentRequestInfoExcelAction(Request $request)
    {

        $date = $request->request->get('dateValue');
        $monthValue = $request->request->get('monthValue');
        $companyType = $request->request->get('companyType');

        $start_date =  date('Y-'.$monthValue.'-d',strtotime($date));
        $end_Date = date('Y-'.$monthValue.'-t',strtotime($date));


        $readyForPayment = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:ReadyForPayment')
            ->getPaymentCompanyMonthlyDetail($start_date,$end_Date,$companyType);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'PO Number',
            'C9'=>'PO Issued Date',
            'D9'=>'PO Issued Month',
            'E9'=>'Project Name',
            'F9'=>'GRN Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'requestPayment'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'monthly payment Request detail Report');

        if(!empty($readyForPayment[0])){
            $index = 11;
            $count = 1;

            foreach($readyForPayment[0] as $detail){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $detail['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $detail['poDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $detail['poDate']->format('F-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $detail['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $detail['requestAmount']);


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
    public function paymentMonthlyReportPaymentInfoExcelAction(Request $request)
    {

        $date = $request->request->get('dateValue');
        $monthValue = $request->request->get('monthValue');
        $companyType = $request->request->get('companyType');

        $start_date =  date('Y-'.$monthValue.'-d',strtotime($date));
        $end_Date = date('Y-'.$monthValue.'-t',strtotime($date));


        $payment = $this->getDoctrine()
            ->getRepository('PmsCoreBundle:Payment')
            ->getPaymentCompanyMonthlyDetail($start_date,$end_Date,$companyType);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'PO Number',
            'C9'=>'PO Issued Date',
            'D9'=>'PO Issued Month',
            'E9'=>'Project Name',
            'F9'=>'Payment Amount',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'Payment'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'monthly payment detail Report');

        if(!empty($payment[0])){
            $index = 11;
            $count = 1;

            foreach($payment[0] as $detail){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $detail['poId']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $detail['poDate']->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $detail['poDate']->format('F-Y'));
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $detail['projectName']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $detail['paymentAmount']);


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

    public function paymentRequestExcelAction(Request $request)
    {

        $keyword = $this->get('request')->query->get('sort');
        $value = $this->get('request')->query->get('direction');
        list($form, $data) = $this->paymentVerifiedSearch($request);
        $readyForPaymentRepository = $this->getDoctrine()->getRepository('PmsCoreBundle:ReadyForPayment');
        $payments = $readyForPaymentRepository->getReadyForPaymentSearch($data,$this->getUser(),$keyword,$value);
        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Request Date',
            'C9'=>'PO No',
            'D9'=>'Grn No',
            'E9'=>'Vendor/Buyer',
            'F9'=>'Payment Amount',
            'G9'=>'Grn Amount',
            'H9'=>'Payment Type',
            'I9'=>'Local/Head',
            'J9'=>'Bill No',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'Payment'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'monthly payment detail Report');

        if(!empty($payments)){
            $index = 11;
            $count = 1;
            /** @var ReadyForPayment $payment */
            foreach($payments as $payment){

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $payment->getCreated()->format('Y-m-d'));
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $payment->getPurchaseOrder()->getId());
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $payment->getGrn() ? $payment->getGrn()->getId(): 'Adv' );
                if($payment->getPurchaseOrder()->getVendor()) {
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $payment->getPurchaseOrder()->getVendor()? $payment->getPurchaseOrder()->getVendor()->getVendorName():'');
                } else {
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $payment->getPurchaseOrder()->getBuyer()? $payment->getPurchaseOrder()->getBuyer()->getUsername():'');
                }
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $payment->getPurchaseOrder()->getNetTotal());
                if($payment->getPurchaseOrder()->getPaymentType() == 'Advance payment'){
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $payment->getPurchaseOrder()->getPaymentType() ? $payment->getPurchaseOrder()->getAdvancePaymentAmount():0);
                } else {
                $objPHPExcel->getActiveSheet()->setCellValue("G".$index, $payment->getAmount()? $payment->getAmount():0);
                }
                $objPHPExcel->getActiveSheet()->setCellValue("H".$index, $payment->getPurchaseOrder()->getPaymentType() ? $payment->getPurchaseOrder()->getPaymentType():'');
                if($payment->getPurchaseOrder()->getPaymentFrom()== 1) {
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index,'Local Office');
                } elseif ($payment->getPurchaseOrder()->getPaymentFrom()== 2){
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index,'Head Office');
                } else {
                $objPHPExcel->getActiveSheet()->setCellValue("I".$index,' ');
                }
                $objPHPExcel->getActiveSheet()->setCellValue("J".$index, $payment->getBillNumber() ? $payment->getBillNumber(): '' );

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
