<?php

namespace Pms\BudgetBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Pms\BudgetBundle\Entity\Budget;
use Pms\UserBundle\Entity\User;

/**
 * PoLog
 *
 * @ORM\Table(name="budget_log")
 * @ORM\Entity(repositoryClass="Pms\BudgetBundle\Entity\Repository\Log\BudgetLogRepository")
 */
class BudgetLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Budget
     *
     * @ORM\ManyToOne(targetEntity="Pms\BudgetBundle\Entity\Budget", inversedBy="budgetLogs")
     * @ORM\JoinColumn(name="budget")
     */
    private $budget;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by")
     */
    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    public function __construct()
    {
        $this->createdDate = new \DateTime();
    }

    /**
     * Set budget
     *
     * @param Budget $budget
     * @return BudgetLog
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return Budget
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return BudgetLog
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return BudgetLog
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return BudgetLog
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
