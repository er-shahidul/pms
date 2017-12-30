<?php

namespace Pms\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pms\CoreBundle\Entity\ReadyForPayment;
use Pms\UserBundle\Entity\User;

/**
 * ReadyForPaymentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReadyForPaymentRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    public function create($purchaseOrder)
    {
        $readyForPayment = new ReadyForPayment();

        $advancePaymentAmount = $purchaseOrder->getAdvancePaymentAmount();
        $readyForPayment->setAmount($advancePaymentAmount);
        $readyForPayment->setVerifiedStatus(0);
        $readyForPayment->setBillNumber($purchaseOrder->getVendorQuotationReferenceNumber());
        $readyForPayment->setBillDate($purchaseOrder->getCreatedDate());
        $readyForPayment->setPurchaseOrder($purchaseOrder);
        $readyForPayment->setCreated(new \DateTime());

        $this->_em->persist($readyForPayment);
        $this->_em->flush();
    }

    public function createReceive($receive)
    {

        $readyForPayment = new ReadyForPayment();
        $purchaseOrder = $receive->getReceiveItems()[0]->getPurchaseOrderItem()->getPurchaseOrder();

        $receiveAmount = 0;
        foreach ($receive->getReceiveItems() as $item) {
            $receiveAmount += $item->getPurchaseOrderItem()->getPrice() * $item->getQuantity();
        }

        $readyForPayment->setAmount($receiveAmount);
        $readyForPayment->setPurchaseOrder($purchaseOrder);
        $readyForPayment->setBillDate($receive->getBillDate());
        $readyForPayment->setBillNumber($receive->getBillNumber());
        $readyForPayment->setGrn($receive);
        $readyForPayment->setCreated(new \DateTime());

        $this->_em->persist($readyForPayment);
        $this->_em->flush();
    }
    public function updateReceiveForReadyPayment($receive)
    {

//        $readyForPayment = new ReadyForPayment();
        $readyForPayment  = $this->findOneBy(array('grn'=>$receive->getId()));

        $purchaseOrder = $receive->getReceiveItems()[0]->getPurchaseOrderItem()->getPurchaseOrder();

        $receiveAmount = 0;
        foreach ($receive->getReceiveItems() as $item) {
            $receiveAmount += $item->getPurchaseOrderItem()->getPrice() * $item->getQuantity();
        }

        $readyForPayment->setAmount($receiveAmount);
        $readyForPayment->setPurchaseOrder($purchaseOrder);
        $readyForPayment->setBillDate($receive->getBillDate());
        $readyForPayment->setBillNumber($receive->getBillNumber());
        $readyForPayment->setGrn($receive);
        $readyForPayment->setCreated(new \DateTime());
        $readyForPayment->setSendBackStatus(NULL);

        $this->_em->flush();
    }

    public function delete($data)
    {
        $this->_em->remove($data);
        $this->_em->flush();
    }

    public function update($data)
    {
        $this->_em->persist($data);
        $this->_em->flush();

        return $this->_em;
    }

    public function updatePaymentId($ready)
    {
        $readyForPayment = $this->createQueryBuilder('rfp');

        foreach($ready as $row){
            $readyForPayment->set('rfp.payment', $row->getPayment(null));
            $readyForPayment->where('rfp.id', $row->getId());
        }

        $this->_em->flush();
    }

    public function updatePayment($paymentList,$payment)
    {
        foreach ($paymentList as $row){
            $readyForPayment  = $this->find($row);
            $readyForPayment->setPayment($payment);
        }

        $this->_em->flush();
    }

    public function advancePaymentVerified($paymentList,$user)
    {

        $readyForPayment  = $this->find($paymentList);
        $readyForPayment->setVerifiedStatus(1);
        $readyForPayment->setVerifiedBy($user);

        $this->_em->flush();
    }

    public function getAdvancePayment($purchaseOrder)
    {
        return $this->findOneBy(array('purchaseOrder'=>$purchaseOrder,'grn'=>NULL));
    }

    public function getAdvancePaid($payment)
    {
        return $this->findOneBy(array('purchaseOrder'=>$payment,'grn'=>NULL));
    }

    public function getAdvanceArchivePaid()
    {
        return $this->findBy(array('grn'=>NULL),array('billDate'=>'DESC'));
    }

    public function vendorPaymentOneReport($data)
    {
        if(!empty($data)) {

            $query= $this->createQueryBuilder('r');
            $query->select('v.vendorName as vendorName');
            $query->addSelect('COUNT(po.id) as NoOfOrder');
            $query->addSelect('r.poPaymentAmount as paidAmount');
            $query->addSelect('r.poAmount as totalPoAmount');
            $query->addSelect('(r.poAmount - r.poPaymentAmount) as duesAmount');
            $query->leftJoin('r.purchaseOrder', 'po');
            $query->leftJoin('r.vendor', 'v');
            $query->where('r.grn IS NOT NULL');

            if(empty($data['start_date']) or empty($data['end_date'])){
                return false;
            }
            $start      = $data['start_date'].' 00:00:01';
            $curDate    = date('Y-m-d h:m:s');
            $start_date = $data['start_date'] ? $start : $curDate;
            $end_date   = $data['end_date'].' 23:59:59';

            $query->andWhere('r.requestDate >= :dateAfter');
            $query->setParameter('dateAfter', $start_date);

            if($data['end_date']) {
                $query->andWhere('r.requestDate <= :dateBefore');
                $query->setParameter('dateBefore', $end_date);
            }if($data['vendor']){
                $query->andWhere('r.vendor = :vendor');
                $query->setParameter('vendor', $data['vendor']);
            }

            $query->groupBy('v.id');
            $query->orderBy('v.vendorName', 'ASC');

            return $query->getQuery()->getResult();

        } else {
            return false;
        }
    }

    public function vendorPaymentTwoReport($data)
    {
        if(!empty($data)) {

            $query = $this->createQueryBuilder('r');
            $query->select('v.vendorName as vendorName');
            $query->addSelect('po.id as poNo');
            $query->addSelect('p.projectName as projectName');
            $query->addSelect('po.paymentType as paymentType');
            $query->addSelect('po.paymentFrom as paymentFrom');
            $query->addSelect('po.paymentMethod as paymentMode');
            $query->addSelect('ptype.name as purchaseType');
            $query->addSelect('rb.name as bankName');
            $query->addSelect('r.cheque as chequeNo');
            $query->addSelect('r.billNumber as billNumber');
            $query->addSelect('r.requestDate as requestDate');
            $query->addSelect('r.billDate as billDate');
            $query->addSelect('r.poPaymentAmount as paidAmount');
            $query->addSelect('r.poAmount as totalPoAmount');
            $query->addSelect('(r.poAmount - r.poPaymentAmount) as duesAmount');
            $query->leftJoin('r.purchaseOrder', 'po');
            $query->leftJoin('po.purchaseOrderItems', 'poi');
            $query->leftJoin('po.poNonpo', 'ptype');
            $query->leftJoin('r.relatedBank', 'rb');
            $query->leftJoin('poi.project', 'p');
            $query->leftJoin('r.vendor', 'v');
            $query->where('r.grn IS NOT NULL');

            if(empty($data['start_date']) or empty($data['end_date'])){
                return false;
            }
            $start      = $data['start_date'].' 00:00:01';
            $curDate    = date('Y-m-d h:m:s');
            $start_date = $data['start_date'] ? $start : $curDate;
            $end_date   = $data['end_date'].' 23:59:59';

            $query->andWhere('r.requestDate >= :dateAfter');
            $query->setParameter('dateAfter', $start_date);

            if($data['end_date']) {
                $query->andWhere('r.requestDate <= :dateBefore');
                $query->setParameter('dateBefore', $end_date);
            }if($data['vendor']){
                $query->andWhere('r.vendor = :vendor');
                $query->setParameter('vendor', $data['vendor']);
            }
            $query->orderBy('v.vendorName', 'ASC');

            return $query->getQuery()->getResult();

        } else {
            return false;
        }
    }

    public function getReadyForPaymentSearch($data,User $user, $keyword = '' , $value = '' )
    {

        $query = $this->createQueryBuilder('rfp');
        $query->join('rfp.purchaseOrder','po');
        $query->leftJoin('rfp.payment','pay');
        $query->leftJoin('po.vendor','v');
        $query->leftJoin('rfp.grn','grn');
        $query->where('rfp.payment is null');
        $query->andWhere('rfp.verifiedStatus is null or rfp.verifiedStatus = 1');
        $query->andWhere('rfp.sendBackStatus is null');


        if(!empty($data['po'])){
            $query->andWhere('po.id = :purchaseOrderId');
            $query->setParameter('purchaseOrderId', $data['po']);
        }

        if(!empty($data['vendor'])){
//            var_dump($data['vendor']);die;
            $query->andWhere('v.id = :vendor');
            $query->setParameter('vendor', $data['vendor']);
        }

        if($keyword && $value ){
            $query->orderBy($keyword, $value);
        }else{
            $query->orderBy('rfp.created', 'desc');
        }

        if($user->hasRole("ROLE_ACCOUNT_ALL") || $user->hasRole("ROLE_SUPER_ADMIN")){
           // $query->andWhere('po.paymentFrom = 2 or po.paymentFrom = 1');
        }elseif($user->hasRole("ROLE_ACCOUNT_HEAD")){
            $query->andWhere('po.paymentFrom = 2');
        }elseif($user->hasRole("ROLE_ACCOUNT_LOCAL")){

            $query->andWhere('po.paymentFrom = 1');
        }
        $query->groupBy('rfp.grn','po.id');
        //return $query;
         return $query->getQuery()->getResult();
    }

    public function getAdvancePaymentLists($data,$keyword ='', $value = '', User $user)
    {
       //  var_dump($data);die;
        $query = $this->createQueryBuilder('rfp');
        $query->leftJoin('rfp.purchaseOrder','po');
        $query->leftJoin('po.vendor','v');
        $query->where('rfp.payment is null');
        $query->andWhere('rfp.verifiedStatus = 0');

        if($data['po']){
            $query->andWhere('po.id = :purchaseOrderId');
            $query->setParameter('purchaseOrderId', $data['po']);
        }
        if($data['vendor']){
            $query->andWhere('po.vendor = :vendor');
            $query->setParameter('vendor', $data['vendor']);
        }

        if($keyword && $value ){
            $query->orderBy($keyword, $value);
        }else{
            $query->orderBy('rfp.created', 'desc');
        }

        if($user->hasRole('"ROLE_ADVANCE_PAYMENT_LOCAL"')){
            $query ->andWhere('po.paymentFrom = 1');
        }elseif($user->hasRole('"ROLE_ADVANCE_PAYMENT_HEAD"')){
            $query ->andWhere('po.paymentFrom = 2');
        }elseif($user->hasRole('"ROLE_ADVANCE_PAYMENT_ALL"') || $user->hasRole('"ROLE_SUPER_ADMIN"')){

        }

        return $query->getQuery()->getResult();
    }

    public function getAdvancePaymentArchiveLists($data,$keyword = '', $value ='', User $user)
    {
        $query = $this->createQueryBuilder('rfp');
        $query->leftJoin('rfp.purchaseOrder','po');
        $query->leftJoin('rfp.payment','pay');
        $query->leftJoin('po.vendor','v');
        $query->where('rfp.verifiedStatus = 1');
//        $query->select('pay.')

        if($data['po']){
            $query->andWhere('po.id = :purchaseOrderId');
            $query->setParameter('purchaseOrderId', $data['po']);
        }
        if($data['vendor']){
            $query->andWhere('po.vendor = :vendor');
            $query->setParameter('vendor', $data['vendor']);
        }

        if($keyword && $value ){
            $query->orderBy($keyword, $value);
        }else{
            $query->orderBy('rfp.created', 'desc');
        }

        if($user->hasRole('"ROLE_ADVANCE_PAYMENT_LOCAL"')){
            $query ->andWhere('po.paymentFrom = 1');
        }elseif($user->hasRole('"ROLE_ADVANCE_PAYMENT_HEAD"')){
            $query ->andWhere('po.paymentFrom = 2');
        }elseif($user->hasRole('"ROLE_ADVANCE_PAYMENT_ALL"') || $user->hasRole('"ROLE_SUPER_ADMIN"')){

        }

        return $query->getQuery()->getResult();
    }

    public function poNoForRequestPaymentAutoComplete($poNo, User $user,$status)
    {
//        var_dump($poNo);die;
        $query = $this->createQueryBuilder('rfp');
        $query->select('po.id');
        $query->join('rfp.purchaseOrder', 'po');
//        $query->join('rfp.verifiedBy', 'u');
        $query->where($query->expr()->like("po.id", "'%$poNo%'"  ));

        if($status =='request'){
            $query->andWhere('rfp.payment is null');
            $query->andWhere('rfp.verifiedStatus is null or rfp.verifiedStatus = 1');
        }else{

        }
        /*if(!$user->hasRole("ROLE_SUPER_ADMIN")){
            $query->join('rfp.verifiedBy', 'u');
            $query->andWhere('u IN(:user)');
            $query->setParameter('user', $user);
        }*/

        $query->orderBy('po.id', 'ASC');
        $query->setMaxResults( '10' );

        return $query->getQuery()->getResult();
    }

    public function getPaymentVerifiedListFromReceiveSection()
    {

        $query = $this->createQueryBuilder('rfp');
        $query->join('rfp.purchaseOrder','po');

        $query->select('rfp.billNumber');
        $query->andWhere('rfp.verifiedStatus = 0');
        $query->orWhere('rfp.verifiedStatus IS NULL');
        $query->groupBy('rfp.billNumber');

        return  $query->getQuery()->getArrayResult();

    }

    public function getPaymentRequestAmount($data)
    {
        if(!empty($data['vendor'])){

            $query = $this->createQueryBuilder('rfp');

            $query->leftJoin('rfp.purchaseOrder', 'po');
            $query->leftJoin('po.vendor','v');
            $query->select('SUM(rfp.amount) as requestedAmount');
            $query->where('rfp.grn is not NULL');
            $query->groupBy('v.id');
            if (!empty($data['vendor'])) {
                $query->andWhere('v.id = :vendor');
                $query->setParameter('vendor', $data['vendor']);
            }
            return $query->getQuery()->getResult();
        } else {
            return false;
        }
    }

    public function sendBackReadyForPayment($rfp)
    {
        foreach ($rfp['readyForPaymentId'] as $row){
            $readyForPayment  = $this->find($row);
            $readyForPayment->setSendBackStatus(3);
            $readyForPayment->setSendBackComments($rfp['sendBackComments']);
        }
        $this->_em->flush();
    }


    public function getPaymentCompanyMonthlyDetail($dateStart,$dateEnd,$companyType)
    {

/*
        $query = $this->createQueryBuilder('rfp');
        $query->join('rfp.purchaseOrder', 'po');
        $query->join('po.purchaseOrderItems','poi');
        $query->join('poi.project','p');
        $query->join('p.projectCategory','pc');
//        $query->select('SUM(rfp.amount) as requestAmount');
        $query->select('SUM(poi.amount) as requestAmount');
        $query->addSelect('p.projectName');
        $query->addSelect('po.id as poId');
        $query->addSelect('po.createdDate as poDate');
        $query->groupBy('po.id');
        $query->orderBy('p.projectName','ASC');
        $this->searchDate($query,$dateStart, $dateEnd);
        $this->handleSearchByCompanyType($companyType,$query);
         $result[] = $query->getQuery()->getResult();
        return $result;*/


        $query = $this->_em->getRepository('PmsCoreBundle:Payment');
        $query = $query->createQueryBuilder('pay');
        $query->join('pay.purchaseOrder', 'po');
        $query->join('po.purchaseOrderItems','poi');
        $query->join('poi.project','p');
        $query->join('p.projectCategory','pc');
    //    $query->select('SUM(poi.amount) as requestAmount');
           $query->select('pay.requestAmount as requestAmount');

        $query->addSelect('po.id as poId');
        $query->addSelect('po.createdDate as poDate');
        $query->addSelect('p.projectName');
        $query->where('pay.status = 2');
        $query->andWhere('pay.paymentDate >= :start');
        $query->andWhere('pay.paymentDate <= :end');
        $query->setParameter('start', $dateStart.' 00:00:00');
        $query->setParameter('end', $dateEnd.' 23:59:59');
        $this->handleSearchByCompanyType($companyType,$query);
        $query->groupBy('po.id','pay.id');
        $result[] = $query->getQuery()->getResult();
        return $result;

    }

    /**
     * @param $dateStartTime
     * @param $dateEndTime
     * @param $query
     */
    protected function handleSearchBetweenDate($dateStartTime, $dateEndTime,$query)
    {

        if (!empty($dateStartTime) && !empty($dateEndTime)) {

            $query->andWhere('po.approvedThreeDate >= :start');
            $query->andWhere('po.approvedThreeDate <= :end');
            $query->setParameter('start', $dateStartTime.' 00:00:00');
            $query->setParameter('end', $dateEndTime.' 23:59:59');
        }
    }

    public function getRequestAmount($data)
    {
        $paymentCompany = array();

        if (!empty($data['year'])) {

            for ($i = 1; 12 >= $i ; $i++) {
                $dateStart = date('Y-' . $i . '-d', (strtotime($data['year'])));
                $dateEnd = date('Y-' . $i . '-t', (strtotime($data['year'])));

                $paymentCompany[$i] = $this->getRequestAmountByDateANDCompanyType($data['companyType'],$dateStart, $dateEnd);
            }

            return $paymentCompany;
        }

    }

    public function getRequestAmountByDateANDCompanyType($companyType,$dateStart, $dateEnd)
    {

        $query = $this->createQueryBuilder('rfp');
        $query->join('rfp.purchaseOrder', 'po');
        $query->join('po.purchaseOrderItems','poi');
        $query->join('poi.project','p');
        $query->join('p.projectCategory','pc');
        $query->select('SUM(rfp.amount) as requestAmount');
//         $query->where('rfp.verifiedStatus is null');

        $this->handleSearchByCompanyType($companyType, $query);
        $this->searchDate($query, $dateStart, $dateEnd);
        return $query->getQuery()->getSingleResult();
    }
    /**
     * @param $query
     * @param $monthStart
     * @param $monthEnd
     */
    protected function searchDate($query, $monthStart, $monthEnd)
    {
        if (!empty($monthStart) && !empty($monthEnd)) {

            $query->andWhere('rfp.created >= :start');
            $query->andWhere('rfp.created <= :end');
            $query->setParameter('start', $monthStart.' 00:00:00');
            $query->setParameter('end', $monthEnd.' 23:59:59');
        }
    }
    /**
     * @param $companyType
     * @param $query
     */
    protected function handleSearchByCompanyType($companyType, $query)
    {
        if (!empty($companyType)) {
            $query->andWhere('p.projectCategory = :companyType');
            $query->setParameter('companyType', $companyType);
        }
    }






}