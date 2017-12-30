<?php

namespace Pms\BudgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\SubCategory;

/**
 * BudgetForSubCategory
 *
 * @ORM\Table(name="budget_for_sub_categories")
 * @ORM\Entity(repositoryClass="Pms\BudgetBundle\Entity\Repository\BudgetForSubCategoryRepository")
 */
class BudgetForSubCategory
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
     * @ORM\ManyToOne(targetEntity="Pms\BudgetBundle\Entity\Budget", inversedBy="budgetForSubCategories", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="budget", nullable=true)
     */
    private $budget;

    /**
     * @var SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\SubCategory", inversedBy="budgetForSubCategory")
     * @ORM\JoinColumn(name="sub_categories", nullable=true)
     */
    private $subCategory;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * Set subCategory
     *
     * @param SubCategory $subCategory
     * @return BudgetForSubCategory
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get subCategory
     *
     * @return SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
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
     * Set amount
     *
     * @param float $amount
     * @return BudgetForSubCategory
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set budget
     *
     * @param Budget $budget
     * @return BudgetForSubCategory
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return BudgetForSubCategory
     */
    public function getBudget()
    {
        return $this->budget;
    }

    public function __toString()
    {
        return $this->getId() . "";
    }
}
