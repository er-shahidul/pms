<?php

namespace Pms\SettingBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Pms\UserBundle\Entity\User;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
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

    public function getProjectForUser(User $user)
    {
        $query = $this->createQueryBuilder('p');
        $query->join('p.users', 'u');
        $query->select('p.id');
        $query->addSelect('p.projectName');
        $query->where('p.status = 1');

        if(!$user->hasRole("ROLE_SUPER_ADMIN")){
            $this->handleSearchByUser($user, $query);
        }

        $query->groupBy('p.id');
        $query->orderBy('p.projectName', 'asc');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $user
     * @param $query
     */
    protected function handleSearchByUser($user, $query)
    {
        if (!empty($user)) {
            $query->andWhere('u IN(:user)');
            $query->setParameter('user', $user);
        }
    }

    public function getProjectSearch($project, $projectType, $area, $status = 'active', $returnQuery = true)
    {
        $query = $this->createQueryBuilder('p');
        $query->orderBy('p.projectName', 'asc');

        if ($project != 0) {
            $query->andWhere('p.id IN(:id)');
            $query->setParameter('id', $project);
        }

        if ($projectType != 0) {
            $query->andWhere('p.projectCategory IN(:projectType)');
            $query->setParameter('projectType', $projectType);
        }

        if ($area != 0) {
            $query->andWhere('p.projectArea IN(:area)');
            $query->setParameter('area', $area);
        }

        $this->handleActiveTypeFilter($status, $query);

        $this->handleDeleteTypeFilter($status, $query);

        if ($returnQuery) {
            return $query;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $status
     * @param $query
     */
    protected function handleActiveTypeFilter($status, $query)
    {
        if ($status == 'active') {
            $query->andWhere('p.status = :status');
            $query->setParameter('status', 1);
        }
    }

    /**
     * @param $status
     * @param $query
     */
    protected function handleDeleteTypeFilter($status, $query)
    {
        if ($status == 'delete') {
            $query->andWhere('p.status = :status');
            $query->setParameter('status', 0);
        }
    }

    public function checkByIsHeadOffice()
    {
        $query = $this->createQueryBuilder('p');
        $query->select('p.id');
        $query->where('p.isHeadOffice = 1');

        return $query->getQuery()->getResult();
    }

    public function findAllProject()
    {
        $query = $this->createQueryBuilder('p');
        $query->select('p.projectName');
        $query->addSelect('p.id');
        $query->where('p.status = 1');
        $query->orderBy('p.projectName', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function allProject()
    {
        $query = $this->createQueryBuilder('p');
        $query->where('p.status = 1');
        $query->orderBy('p.projectName', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function getAccessUserBudgetForProjectManager($project)
    {
        $query = $this->createQueryBuilder('p');
        $query->leftJoin('p.projectHead','u');
        $query->leftJoin('u.profile', 'pro');
        $query->select('pro.cellphone');
        $query->where('p.id = :project');
        $query->setParameter('project', $project);
        return $query->getQuery()->getResult();
    }
}
