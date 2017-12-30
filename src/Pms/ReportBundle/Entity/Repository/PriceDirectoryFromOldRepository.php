<?php

namespace Pms\ReportBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PriceDirectoryFromOldRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PriceDirectoryFromOldRepository extends EntityRepository
{
    public function getPurchaseOrderPriceDirectoryOld($data, $returnQuery = true)
    {
        $query = $this->createQueryBuilder('pdo');
        $query->where('pdo.approveStatus = 3');

        if (!empty($data['projectName'])) {
            $projectName = $this->_em->getRepository('PmsSettingBundle:Project')->find($data['projectName']);
            $query->andWhere("pdo.projectName = :project");
            $query->setParameter('project', $projectName->getProjectName());
        }
        if (!empty($data['itemName'])) {
            $itemName = $this->_em->getRepository('PmsSettingBundle:Item')->find($data['itemName']);
            $query->andWhere("pdo.itemName = :item");
            $query->setParameter('item', $itemName->getItemName());
        }
        if (!empty($data['itemType'])) {
            $itemType = $this->_em->getRepository('PmsSettingBundle:ItemType')->find($data['itemType']);
            $query->andWhere("pdo.itemType = :itemType");
            $query->setParameter('itemType', $itemType->getItemType());
        }
        if(!empty($data['end_date']) && !empty($data['start_date'])) {
            $query->andWhere('pdo.createdDate >= :start_date');
            $query->andWhere('pdo.createdDate <= :end_date');
            $query->setParameter('start_date', $data['start_date'].' 00:00:01');
            $query->setParameter('end_date', $data['end_date'] .' 23:59:59');
        }

        if(empty($data)){
            $query->where('pdo.approveStatus = 9');
        }

        $query->orderBy('pdo.createdDate', 'DESC');

        if ($returnQuery) {
            return $query;
        }

        return $query->getQuery()->getResult();
    }
}