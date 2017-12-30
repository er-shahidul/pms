<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * SubCategory
 *
 * @ORM\Table(name="sub_categories")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\SubCategoryRepository")
 */
class SubCategory
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
     * @var string
     *
     * @ORM\Column(name="sub_categories_name", type="string", length=255)
     */
    private $subCategoryName;

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
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Category", inversedBy="subCategories", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="categories", nullable=true)
     */
    private $category;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\SettingBundle\Entity\CostHeader", mappedBy="subCategory")
     */
    private $costHeader;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\BudgetForSubCategory", mappedBy="subCategory")
     */
    private $budgetForSubCategory;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\AdditionalBudgetForSubCategory", mappedBy="subCategory")
     */
    private $additionalBudgetForSubCategory;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisition", mappedBy="subCategory")
     */
    private $purchaseRequisition;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\SupplierBundle\Entity\Supplier", mappedBy="subCategory")
     */
    private $suppliers;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="category_heads", nullable=true)
     */
    private $head;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="category_sub_heads", nullable=true)
     */
    private $subHead;

    /**
     * Set head
     *
     * @param User $head
     * @return SubCategory
     */
    public function setHead($head)
    {
        $this->head = $head;

        return $this;
    }

    /**
     * Get head
     *
     * @return User
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * Set subHead
     *
     * @param User $subHead
     * @return SubCategory
     */
    public function setSubHead($subHead)
    {
        $this->subHead = $subHead;

        return $this;
    }

    /**
     * Get subHead
     *
     * @return User
     */
    public function getSubHead()
    {
        return $this->subHead;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseRequisition()
    {
        return $this->purchaseRequisition;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBudgetForSubCategory()
    {
        return $this->budgetForSubCategory;
    }

    /**
     * Set category
     *
     * @param Category $category
     * @return SubCategory
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCostHeader()
    {
        return $this->costHeader;
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
     * Set subCategoryName
     *
     * @param string $subCategoryName
     * @return Category
     */
    public function setSubCategoryName($subCategoryName)
    {
        $this->subCategoryName = $subCategoryName;

        return $this;
    }

    /**
     * Get subCategoryName
     *
     * @return string
     */
    public function getSubCategoryName()
    {
        return $this->subCategoryName;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return SubCategory
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
     * @return SubCategory
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
     * @return SubCategory
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
     * @return ArrayCollection
     */
    public function getAdditionalBudgetForSubCategory()
    {
        return $this->additionalBudgetForSubCategory;
    }

    /**
     * @return ArrayCollection
     */
    public function getSuppliers()
    {
        return $this->suppliers;
    }

    /**
     * @param ArrayCollection $suppliers
     */
    public function setSuppliers($suppliers)
    {
        $this->suppliers = $suppliers;
    }
}
