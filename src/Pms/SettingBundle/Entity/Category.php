<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\CategoryRepository")
 */
class Category
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
     * @ORM\ManyToMany(targetEntity="Pms\SettingBundle\Entity\Item", mappedBy="category")
     **/
    private $item;

    /**
     * @var string
     *
     * @ORM\Column(name="categories_name", type="string", length=255)
     */
    private $categoryName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisition", mappedBy="category")
     */
    private $purchaseRequisition;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\SupplierBundle\Entity\Supplier", mappedBy="category")
     */
    private $suppliers;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\SettingBundle\Entity\SubCategory", mappedBy="category", cascade={"persist", "remove"})
     */
    private $subCategories;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
        $this->item = new ArrayCollection();
    }

    public function addSubCategory(SubCategory $subCategory)
    {
        $subCategory->setCategory($this);

        if (!$this->getSubCategories()->contains($subCategory)) {
            $this->subCategories->add($subCategory);
        }

        return $this;
    }

    public function removeSubCategory(SubCategory $subCategory)
    {
        if ($this->getSubCategories()->contains($subCategory)) {
            $this->getSubCategories()->removeElement($subCategory);
        }
    }

    public function getSubCategories()
    {
        return $this->subCategories;
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
     * Set categoryName
     *
     * @param string $categoryName
     * @return Category
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get categoryName
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseRequisition()
    {
        return $this->purchaseRequisition;
    }

    public function addItem(Item $item)
    {
        $item->addCategory($this);

        if (!$this->getItem()->contains($item)) {
            $this->item->add($item);
        }

        return $this;
    }

    public function removeItem(Item $item)
    {
        if ($this->getItem()->contains($item)) {
            $this->getItem()->removeElement($item);
        }
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
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
