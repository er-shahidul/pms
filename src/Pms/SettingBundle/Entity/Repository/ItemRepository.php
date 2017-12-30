<?php

namespace Pms\SettingBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ItemRepository extends EntityRepository
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

    public function getQueryByProjectId($project)
    {
        $query = $this->createQueryBuilder('i');
        $query->innerJoin('PmsInventoryBundle:TotalReceiveItem', 't', 'WITH', 't.item = i');
        $query->where('t.project = :project');
        $query->setParameter('project', $project);
        $query->distinct(true);
        $query->orderBy('i.itemName','ASC');

        return $query;
    }

    public function getCategories($item)
    {
        $query =  $this->createQueryBuilder('i');
        $query->select('c.id');
        $query->addSelect('c.categoryName');
        $query->where('i.id = :item');
        $query->andWhere('i.status = 1');
        $query->setParameter('item', $item);
        $query->join('i.category', 'c');

        return $query->getQuery()->getResult();
    }

    public function getCategoryBasedItems($category)
    {
        $query = $this->createQueryBuilder('i');
        $query->select('i.id');
        $query->addSelect('i.itemName');
        $query->where('c.id = :category');
        $query->andWhere('i.status = 1');
        $query->setParameter('category', $category);
        $query->join('i.category', 'c');
        $query->orderBy('i.itemName', 'asc');

        return $query->getQuery()->getResult();
    }

    public function getItemTypes($item)
    {
        $query = $this->createQueryBuilder('i');
        $query->select('it.itemType');
        $query->addSelect('i.itemUnit');
        $query->addSelect('i.price');
        $query->where('i.id = :item');
        $query->andWhere('i.status = 1');
        $query->setParameter('item', $item);
        $query->join('i.itemType', 'it');

        return $query->getQuery()->getResult();
    }

    public function getItemSearch($item, $itemType, $type = 'active', $returnQuery = true)
    {
        $query = $this->createQueryBuilder('i');
        $query->orderBy('i.itemName', 'asc');

        if ($item != 0 && $itemType != 0) {
            $query->andWhere('i.id IN(:item)');
            $query->setParameter('item', $item);
            $query->andWhere('i.itemType IN(:itemType)');
            $query->setParameter('itemType', $itemType);
        }elseif ($item != 0) {
            $query->andWhere('i.id IN(:item)');
            $query->setParameter('item', $item);
        }elseif ($itemType != 0) {
            $query->andWhere('i.itemType IN(:itemType)');
            $query->setParameter('itemType', $itemType);
        }

        $this->handleActiveTypeFilter($type, $query);

        $this->handleDeleteTypeFilter($type, $query);

        if ($returnQuery) {
            return $query;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $type
     * @param $query
     */
    protected function handleActiveTypeFilter($type, $query)
    {
        if ($type == 'active') {
            $query->andWhere('i.status = :status');
            $query->setParameter('status', 1);
        }
    }

    /**
     * @param $type
     * @param $query
     */
    protected function handleDeleteTypeFilter($type, $query)
    {
        if ($type == 'delete') {
            $query->andWhere('i.status = :status');
            $query->setParameter('status', 0);
        }
    }

    public function allItem()
    {
        $query = $this->createQueryBuilder('i');
        $query->where('i.status = 1');
        $query->orderBy('i.itemName', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function itemAutoComplete($itemName, $user)
    {
        $query = $this->createQueryBuilder('i');
        $query->select('i.id');
        $query->addSelect('i.itemName as text');
        $query->leftJoin('i.createdBy', 'u');
        $query->where($query->expr()->like("i.itemName", "'$itemName%'"  ));
        /*if(!$user->hasRole("ROLE_SUPER_ADMIN") or !$user->hasRole("ROLE_PURCHASE_ORDER_APPROVE_USERS")){
            $query->andWhere($query->expr()->eq("i.createdBy", "'$user'"  ));
        }*/
        $query->orderBy('i.itemName', 'ASC');
        $query->setMaxResults( '200' );

        return $query->getQuery()->getResult();
    }
    public function itemAutoCompleteForPr($itemName, $user,$category)
    {

        $query = $this->createQueryBuilder('i');
        $query->join('i.category','ca');
        $query->select('i.id');
        $query->addSelect('i.itemName as text');
        $query->leftJoin('i.createdBy', 'u');
        $query->where($query->expr()->like("i.itemName", "'$itemName%'"  ));
        $query->andWhere('ca.id IS NOT NULL');
        $this->categoryWiseItemFilter($category,$query);
        $query->andWhere('i.status = 1');
        $query->orderBy('i.itemName', 'ASC');
        $query->setMaxResults( '10' );

        return $query->getQuery()->getResult();
    }
    protected function categoryWiseItemFilter($category, $query)
    {
        if (!empty($category)) {
            $query->andWhere('ca.id = :category');
            $query->setParameter('category', $category);
        }
    }

    public function itemDeliveryAutoComplete($itemName)
    {

        $query = $this->createQueryBuilder('i');
        $query->select('i.id');
        $query->addSelect('i.itemName as text');
        $query->where($query->expr()->like("i.itemName", "'$itemName%'"  ));
        $query->orderBy('i.itemName', 'ASC');
        $query->setMaxResults( '10' );

        return $query->getQuery()->getResult();
    }
}
