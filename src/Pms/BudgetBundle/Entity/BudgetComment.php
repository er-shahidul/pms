<?php

namespace Pms\BudgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * BudgetComment
 *
 * @ORM\Table(name="budget_comments")
 * @ORM\Entity(repositoryClass="Pms\BudgetBundle\Entity\Repository\BudgetCommentRepository")
 */
class BudgetComment
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
     * @ORM\ManyToOne(targetEntity="Pms\BudgetBundle\Entity\Budget", inversedBy="budgetComment")
     * @ORM\JoinColumn(name="budget")
     */
    private $budget;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var text
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comment;

    /**
     * Set comment
     *
     * @param  $comment
     * @return BudgetComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return text
     */
    public function getComment()
    {
        return $this->comment;
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

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return BudgetComment
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
     * @return BudgetComment
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
     * Set budget
     *
     * @param Budget $budget
     * @return BudgetComment
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
}
