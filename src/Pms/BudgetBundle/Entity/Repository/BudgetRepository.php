<?php

namespace Pms\BudgetBundle\Entity\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Pms\CoreBundle\Entity\PurchaseRequisition;
use Pms\UserBundle\Entity\User;

/**
 * BudgetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BudgetRepository extends EntityRepository
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

    public function getBudgetVsPRGraphData(User $user, $em, $type = 'open', $returnQuery = true)
    {
        if($type =='open'){
            $status = 1;

            $budgetQuery = $this
                ->createQueryBuilder('b')
                ->select('b.month')
                ->addSelect('b.netTotal')
                ->addSelect('b.createdDate')
                ->orderBy('b.id', 'DESC')
                ->setMaxResults('12');
            $sql      = $budgetQuery->getQuery()->getResult();
        }

        return $sql;
    }

    public function getBudgetSearch(User $user, $dataDate, $data, $type = 'all', $returnQuery = true)
    {
        $query = $this->createQueryBuilder('b');
        $query->join('b.budgetForSubCategories', 'bs');
        $query->join('b.project', 'p');
        $query->join('p.users', 'u');
        $query->orderBy('b.createdDate', 'desc');

        if(!$user->hasRole("ROLE_SUPER_ADMIN")){
            $query->andWhere('u IN(:user)');
            $query->setParameter('user', $user);
        }

        if (!empty($dataDate['month']) && !empty($data['project']) ) {
            $query->andWhere('b.month =(:month)');
            $query->setParameter('month', $dataDate['month']);
            $query->andWhere('b.project =(:project)');
            $query->setParameter('project', $data['project']);
        }elseif (!empty($dataDate['month']) && empty($data['project'])) {
            $query->andWhere('b.month =(:month)');
            $query->setParameter('month', $dataDate['month']);
        }elseif (empty($dataDate['month']) && !empty($data['project']) ) {
            $query->andWhere('b.project =(:project)');
            $query->setParameter('project', $data['project']);
        }else{
            $month = new DateTime();
            $monthStart = $month->format('Y-m-01');
            $monthEnd = $month->format('Y-m-t');

            $query->andWhere('b.month >= :monthStart');
            $query->andWhere('b.month <= :monthEnd');
            $query->setParameter('monthStart', $monthStart);
            $query->setParameter('monthEnd', $monthEnd);
        }

        $this->handleAllTypeFilter($type, $query);

        $this->handleHoldTypeFilter($type, $query);

        $this->handleCancelTypeFilter($type, $query);

        if ($returnQuery) {
            return $query;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $type
     * @param $query
     */
    protected function handleAllTypeFilter($type, $query)
    {
        if ($type == 'all') {
            $query->andWhere('b.status = :status');
            $query->setParameter('status', 1);
        }
    }

    /**
     * @param $type
     * @param $query
     */
    protected function handleHoldTypeFilter($type, $query)
    {
        if ($type == 'hold') {
            $query->andWhere('b.status = :status');
            $query->setParameter('status', 5);
        }
    }

    /**
     * @param $type
     * @param $query
     */
    protected function handleCancelTypeFilter($type, $query)
    {
        if ($type == 'cancel') {
            $query->andWhere('b.status = :status');
            $query->setParameter('status', 6);
        }
    }

    public function getBudgetMonthlyCost($month)
    {


        $sql = "SELECT
                    budgets.project as id,
                    budget_for_sub_categories.sub_categories as subId,
                    sub_categories.sub_categories_name,
                    budget_for_sub_categories.amount

                    FROM budgets INNER JOIN budget_for_sub_categories ON budgets.id = budget_for_sub_categories.budget
                         INNER JOIN sub_categories ON budget_for_sub_categories.sub_categories = sub_categories.id
                    WHERE budgets.created_date LIKE '%$month%'
                    ORDER BY budgets.project ASC LIMIT 2";
        $result = $this->_em->getConnection()->fetchAll($sql);

        $data = array();
        foreach($result as $project) {
            $data[$project['id']][$project['subId']] = $project;
        }

        return $data;
    }

    public function getBudgetMonthlyCost_($data)
    {
        $month = $data['month'];
        $qb    = $this->createQueryBuilder('b')
            ->join('b.project', 'p')
            ->select('p.projectName')
            ->addSelect('p.id')
            ->addSelect('b.month')
            ->addSelect('b.netTotal');
        $qb ->Where('p.id = :projectId');
        $qb ->andWhere($qb->expr()->like("b.month", "'%$month%'"  ));
        $qb->setParameter('projectId',$data['project_id']);
        return  $qb->getQuery()->getResult();
    }

    public function getBudgetTotalForPrFormMSG($month, $project, $subCategory)
    {
        $month = strtotime($month);
        $month = date('Y-m-d',$month);

        $query = $this->createQueryBuilder('b');
//        $query->join('b.budgetForSubCategories', 'bs');
        $query->join('b.project', 'p');
        $query->select('b.netTotal');
//        $query->select('bs.amount');
        $query->where('b.status = 1');
        $query->andWhere('b.month = :month');
        $query->andWhere('b.project = :project');
//        $query->andWhere('bs.id = :subCategory');
        $query->andWhere('b.approveStatus = 3');
        $query->setParameter('month', $month);
        $query->setParameter('project', $project);
//        $query->setParameter('subCategory', $subCategory);
//var_dump($query->getQuery()->getResult());die;
        return $query->getQuery()->getResult();
    }

    public function getBudgetReport($data)
    {
        $query = $this->createQueryBuilder('b');
        $query->join('b.budgetForSubCategories', 'bs');
        $query->join('b.project', 'p');
        $query->join('p.users', 'u');
        $query->where('b.status = 1');
        $query->andWhere('b.approveStatus = 3');

        if (!empty($data['month'])) {
            $query->andWhere('b.month =(:month)');
            $query->setParameter('month', $data['month']);
        }else{
            $month = new DateTime();
            $monthStart = $month->format('Y-m-01');
            $monthEnd = $month->format('Y-m-t');

            $query->andWhere('b.month >= :monthStart');
            $query->andWhere('b.month <= :monthEnd');
            $query->setParameter('monthStart', $monthStart);
            $query->setParameter('monthEnd', $monthEnd);
        }

        return $query->getQuery()->getResult();
    }

    public function getBudgetTotalReport($data)
    {
        $query = $this->createQueryBuilder('b');
        $query->select('SUM(b.netTotal) as totalBudget');
        $query->where('b.status = 1');
        $query->andWhere('b.approveStatus = 3');

        if (!empty($data['month'])) {
            $query->andWhere('b.month =(:month)');
            $query->setParameter('month', $data['month']);
        }else{
            $month = new DateTime();
            $monthStart = $month->format('Y-m-01');
            $monthEnd = $month->format('Y-m-t');

            $query->andWhere('b.month >= :monthStart');
            $query->andWhere('b.month <= :monthEnd');
            $query->setParameter('monthStart', $monthStart);
            $query->setParameter('monthEnd', $monthEnd);
        }

        return $query->getQuery()->getResult();
    }

    public function getBudgetReportYearly($data)
    {
        $totalMonthlyBudget = array();
        $totalMonthlyBudgetSpend = array();
        $totalSavingsOrSpendOverBudget = array();
        $month = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December');

        if (!empty($data['year'])) {

            for ($i = 1; $i < 13; $i++) {
                $dateStart = date('Y-' . $i . '-d', (strtotime($data['year'])));
                $dateEnd = date('Y-' . $i . '-t', (strtotime($data['year'])));

                $totalMonthlyBudget[]      = $this->totalMonthlyBudget($dateStart, $dateEnd);
                $totalMonthlyBudgetSpend[] = $this->_em->getRepository('PmsCoreBundle:PurchaseOrderItem')
                                                  ->totalMonthlyBudgetSpend($dateStart, $dateEnd);
            }

            for ( $i = 0; $i < 12; ++$i ) {
                $totalSavingsOrSpendOverBudget[] = $totalMonthlyBudget[$i]['totalBudget'] - $totalMonthlyBudgetSpend[$i]['totalBudgetSpend'];
            }
        }

        return array($totalMonthlyBudget, $totalMonthlyBudgetSpend, $totalSavingsOrSpendOverBudget, $month);
    }

    private function totalMonthlyBudget($dateStart, $dateEnd)
    {
        $query = $this->createQueryBuilder('b');
        $query->select('SUM(b.netTotal) as totalBudget');
        $query->where('b.status = 1');
        $query->andWhere('b.approveStatus = 3');

        $query->andWhere('b.month >= :monthStart');
        $query->andWhere('b.month <= :monthEnd');
        $query->setParameter('monthStart', $dateStart);
        $query->setParameter('monthEnd', $dateEnd);

        return $query->getQuery()->getSingleResult();
    }

    public function getTotalBudgetByPrProjectAndSubCategoryWise(PurchaseRequisition $purchaseRequisition)
    {
                $year = $purchaseRequisition->getCreatedDate()->format('Y');
                $getMonth = $purchaseRequisition->getCreatedDate()->format('m');
                $month = date($year.'-' .$getMonth . '-01');

                $query = $this->createQueryBuilder('b');
                $query->join('b.project','p');
                $query->join('b.budgetForSubCategories','bfsc');
                $query->join('bfsc.subCategory','sc');
                $query->select('SUM(bfsc.amount) as totalSubCategoryBudgetAmount');
                $query->where('b.status = 1');
                $query->andWhere('b.approveStatus = 3');
                $query->andWhere('p.id  = :project');
                $query->andWhere('sc.id = :SubCategory');
                $query->setParameter('project', $purchaseRequisition->getProject()->getId());
                $query->setParameter('SubCategory', $purchaseRequisition->getSubCategory()->getId());
                $query->andWhere('b.month = :month');
                $query->setParameter('month', $month);

        return $query->getQuery()->getSingleResult();
    }
}