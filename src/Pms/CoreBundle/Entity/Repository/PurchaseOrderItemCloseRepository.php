<?php

namespace Pms\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PurchaseOrderItemCloseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PurchaseOrderItemCloseRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    public function create($data)
    {
        $this->_em->persist($data);
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

    public function getPurchaseRequisitionIndividualData($data)
    {
        list($start_date, $end_date) = $this->monthStartEnd($data);

        $query = $this->createQueryBuilder('pric');
        $query->join('pric.purchaseRequisitionItem','pri');
        $query->join('pri.purchaseRequisition','pr');
        $query->andWhere('pr.status = 6 ');
        $query->andWhere('pr.totalOrderItemQuantity = 0');
        $query->orderBy('pric.closedDate','DESC');

        $this->handleSearchBetweenDate($query,$start_date,$end_date);

       // $this->handleIndividualCancelTypeFilter($type, $query);

        return $query->getQuery()->getResult();


    }

    /**
     * @param $query
     * @param $monthStart
     * @param $monthEnd
     */
    protected function handleSearchBetweenDate($query, $monthStart, $monthEnd)
    {
        if (!empty($monthStart) && !empty($monthEnd)) {
            $query->andWhere('pr.createdDate >= :start');
            $query->andWhere('pr.createdDate <= :end');
            $query->setParameter('start', $monthStart.' 00:00:01');
            $query->setParameter('end', $monthEnd.' 23:59:59');
        }
    }
    /**
     * @param $type
     * @param $query
     */
    protected function handleIndividualCancelTypeFilter($type, $query)
    {
        if ($type == 'cancel') {
            $query->andWhere('pr.status = :cancel');
            $query->setParameter('cancel', 6);
            $query->andWhere('pr.status != :create');
            $query->setParameter('create', 1);
            $query->andWhere('pr.status != :hold');
            $query->setParameter('hold', 5);
        }
    }

    protected function monthStartEnd($data)
    {
        $month = $data["month"];
        $start_date = $month . ' 00:00:01';
        $end_date = date('Y-m-t 23:59:59', (strtotime($start_date)));

        return array($start_date, $end_date);
    }
}
