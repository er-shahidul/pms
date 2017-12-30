<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Pms\UserBundle\Entity\User;

/**
 * Item
 *
 * @ORM\Table(name="items")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\ItemRepository")
 */
class Item
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
     * @var Category
     *
     * @ORM\ManyToMany(targetEntity="Pms\SettingBundle\Entity\Category", inversedBy="item")
     * @ORM\JoinTable(name="item_categories",
     *      joinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="categories_id", referencedColumnName="id")}
     * )
     * @ORM\JoinColumn(name="categories")
     */
    private $category;

    /**
     * @var ItemType
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\ItemType", inversedBy="item")
     * @ORM\JoinColumn(name="item_types")
     */
    private $itemType;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisitionItem", mappedBy="item")
     */
    private $purchaseRequisitionItem;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\DeliveryItem", mappedBy="item")
     */
    private $deliveryItem;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderItem", mappedBy="item")
     */
    private $purchaseOrderItem;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\TotalReceiveItem", mappedBy="item")
     */
    private $totalReceiveItem;
    /**
         * @var ArrayCollection
         *
         * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\DailyInventory", mappedBy="item")
         */
    private $dailyInventory;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReceivedItem", mappedBy="item")
     */
    private $receivedItem;

    /**
     * @var string
     *
     * @ORM\Column(name="items_name", type="string", length=255)
     */
    private $itemName;

    /**
     * @var string
     *
     * @ORM\Column(name="item_unit", type="string", length=255)
     */
    private $itemUnit;

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
     * @var float
     *
     * @ORM\Column(name="prices", type="float")
     */
    private $price;

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
     * Set itemName
     *
     * @param string $itemName
     * @return Item
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;

        return $this;
    }

    /**
     * Get itemName
     *
     * @return string 
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function addCategory(Category $category)
    {
        $category->addItem($this);

        if (!$this->getCategory()->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category)
    {
        if ($this->getCategory()->contains($category)) {
            $this->getCategory()->removeElement($category);
        }
    }

    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set itemType
     *
     * @param ItemType $itemType
     * @return ProjectCostItem
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * Get itemType
     *
     * @return ItemType
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Set itemUnit
     *
     * @param string $itemUnit
     * @return Item
     */
    public function setItemUnit($itemUnit)
    {
        $this->itemUnit = $itemUnit;

        return $this;
    }

    /**
     * Get itemUnit
     *
     * @return string
     */
    public function getItemUnit()
    {
        return $this->itemUnit;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Item
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return Item
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
     * @return Item
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
     * @return Item
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
    public function getPurchaseRequisitionItem()
    {
        return $this->purchaseRequisitionItem;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseOrderItem()
    {
        return $this->purchaseOrderItem;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function geReceivedItem()
    {
        return $this->receivedItem;
    }

    public function __toString()
    {
        return $this->getId();
    }

    /**
     * @return ArrayCollection
     */
    public function getDeliveryItem()
    {
        return $this->deliveryItem;
    }

    /**
     * @return ArrayCollection
     */
    public function getTotalReceiveItem()
    {
        return $this->totalReceiveItem;
    }

    /**
     * @return ArrayCollection
     */
    public function getDailyInventory()
    {
        return $this->dailyInventory;
    }
}
