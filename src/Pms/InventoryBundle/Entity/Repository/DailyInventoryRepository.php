<?php

namespace Pms\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DailyInventoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DailyInventoryRepository extends EntityRepository
{
    public function getAll()
    {
        $getTotalItem = $this->createQueryBuilder('tri')
            ->join('tri.item', 'i')
            ->select('i.itemName')
            ->addSelect('i.id')
            ->orderBy('i.itemName','ASC');

        return $getTotalItem->getQuery()->getResult();
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

    public function getStockStatusReport($data)
    {

        if(!empty($data)){

           $qb = $this->createQueryBuilder('di');


            $qb->orderBy('di.createdDate', 'desc');
            if(empty($data['start_date']) or empty($data['end_date'])){
                return false;
            }

            $start      = $data['start_date'].' 00:00:00';
            $curDate    = date('Y-m-d h:m:s');
            $start_date = $data['start_date'] ? $start : $curDate;
            $end_date   = $data['end_date'].' 23:59:59';
            $qb ->where('di.createdDate >= :dateAfter');
            $qb->setParameter('dateAfter', $start_date);

            if($data['end_date']) {
                $qb->andWhere('di.createdDate <= :dateBefore');
                $qb->setParameter('dateBefore', $end_date);
            }
            if(!empty($data['item'])){
                $qb->leftJoin("di.item" ,'i');
                $qb->andWhere($qb->expr()->eq("di.item", $data['item'] ));
            }
            if(!empty($data['project'])){
                $qb->leftJoin("di.project" ,'p');
                $qb->andWhere($qb->expr()->eq("di.project", $data['project'] ));
            }
            return $qb->getQuery()->getResult();
         //   return $qb;

        } else{
            return false;
        }


    }

    public function existingDailyItemCheck($project, $item)
    {
        $date = date('Y-m-d');
        $qb = $this->createQueryBuilder('di');
        $qb->join('di.item', 'i');
        $qb->select('di.id');
        $qb->where("di.project= :project");
        $qb->andWhere("di.item = :item");
        $qb->andWhere($qb->expr()->like("di.createdDate", "'%$date%'"  ));
        $qb->setParameter('project', $project);
        $qb->setParameter('item', $item);
        return $qb->getQuery()->getResult();
    }

    public function getTotalIssuingQty($data)
    {
        $qb = $this->createQueryBuilder('di');
        $qb ->leftJoin('di.project','p')
            ->leftJoin('di.item','i')
            ->select('SUM(di.issuingQuantity) as issuingQuantity')
            ->addSelect('i.id as itemId')
            ->addSelect('di.createdDate');
        $qb->andWhere('di.createdDate <= :start_date');
        $qb->setParameter('start_date',$data['start_date'].' '.'00:00:00');
        $this->filteringByProject($data, $qb);
        $this->filteringByItem($data,$qb);
        $qb->groupBy('di.item');
        $qb->orderBy('i.itemName','ASC');
        $results = $qb->getQuery()->getResult();

        $data = array();
        foreach($results as $result ){
            $data[$result['itemId']] = $result;
        }
        return $data;
    }

    public function getTotalReceiveByProjectQty($project,$item){

        $query = $this->createQueryBuilder('di');
        $query->join('di.project', 'p');
        $query->join('di.item', 'i');
        $query->select('SUM(di.issuingQuantity) as itemUsageQuantity');

        $this->filteringByProject($project, $query);
        $this->filteringByItem($item, $query);

        return $query->getQuery()->getSingleResult();
    }
    public function getIssuingQty($item,$project)
    {
        $qb = $this->createQueryBuilder('di');
        $qb ->leftJoin('di.project','p')
            ->leftJoin('di.item','i')
            ->select('di.issuingQuantity');
        $qb->andWhere('p.id = :project');
        $qb->setParameter('project', $project);
        $qb->andWhere('i.id = :item');
        $qb->setParameter('item', $item);
        $qb->groupBy('di.project');
        $results = $qb->getQuery()->getResult();
        return $results;
    }
    public function filteringByItem($data,$qb){
        if(!empty($data['item'])){
            $qb->andWhere('i.id = :item');
            $qb->setParameter('item', $data['item']);
        }
    }

    /**
     * @param $data
     * @param $qb
     */
    private function filteringByProject($data, $qb)
    {
        if(!empty($data['project'])) {

            $qb->andWhere('p.id = :project');
            $qb->setParameter('project', $data['project']);
        }
    }

    public function totalIssueQty($data)
    {
        $qb = $this->createQueryBuilder('di');
        $qb ->leftJoin('di.project','p')
            ->leftJoin('di.item','i')
            ->leftJoin('i.category','c')
            ->select('SUM(di.issuingQuantity) as issueQty')
            ->addSelect('i.id as itemId');
        $this->filteringByProject($data, $qb);
        $this->handleSearchByCategory($data['category'],$qb);
        $this->handleSearchBetweenDate($data['start_date'],$data['end_date'],$qb);
        $qb->groupBy('i.id');
        $results = $qb->getQuery()->getResult();

        $data = array();
        foreach($results as $row ){
            $data[$row['itemId']] = $row;
        }
        return $data;
    }
    private function handleSearchByCategory($category, $query)
    {

        if (!empty($category)) {
            $query->andWhere('c.id = :category');
            $query->setParameter('category', $category);
        }
    }
    /**
     * @param $dateStartTime
     * @param $dateEndTime
     * @param $query
     */
    protected function handleSearchBetweenDate($dateStartTime, $dateEndTime, $query)
    {
        if (!empty($dateStartTime) && !empty($dateEndTime)) {
            $query->andWhere('di.createdDate >= :start');
            $query->andWhere('di.createdDate <= :end');
            $query->setParameter('start', $dateStartTime.' 00:00:00');
            $query->setParameter('end', $dateEndTime.' 23:59:59');
        }
    }
}