<?php

namespace Pms\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Entity\Project;

/**
 * TotalReceiveItem
 *
 * @ORM\Table(name="total_receive_items")
 * @ORM\Entity(repositoryClass="Pms\InventoryBundle\Entity\Repository\TotalReceiveItemRepository")
 */
class TotalReceiveItem
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
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="totalReceiveItem")
     * @ORM\JoinColumn(name="project_id", nullable=true)
     */
    private $project;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project")
     * @ORM\JoinColumn(name="project_to", nullable=true)
     */
    private $projectTo;

    /**
     * @var item
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Item", inversedBy="totalReceiveItem")
     * @ORM\JoinColumn(name="item_id", nullable=true)

     */
    private $item;

    /**
     * @var float
     *
     * @ORM\Column(name="total_item", type="float",nullable=true )
     */
    private $totalItem = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="receive_by_project_quantity", type="float",nullable = true )
     */
    private $receiveByProjectQty = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="total_used_receive_item", type="float",nullable=true)
     */
    private $totalUsedItem = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_stock_count", type="integer",nullable=true)
     */
    private $minStockCount = 0 ;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_stock_count", type="integer",nullable=true)
     */
    private $maxStockCount = 0 ;

    /**
     * @var integer
     *
     * @ORM\Column(name="open_item", type="integer",nullable=true)
     */
    private $openItem = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="extra_added_item_quantity", type="float",nullable=true )
     */
    private $extraAddedItemQuantity = 0;
    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float",nullable=true )
     */
    private $price = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="opening_date", type="datetime", nullable=true)
     */
    private $openingDate;

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
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param item $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return float
     */
    public function getTotalItem()
    {
        return $this->totalItem;
    }

    /**
     * @param float $totalItem
     */
    public function setTotalItem($totalItem)
    {
        $this->totalItem = $totalItem;
    }

    /**
     * @return float
     */
    public function getTotalUsedItem()
    {
        return $this->totalUsedItem;
    }

    /**
     * @param float $totalUsedItem
     */
    public function setTotalUsedItem($totalUsedItem)
    {
        $this->totalUsedItem = $totalUsedItem;
    }

    public function getRemainingQuantity() {

        return ( $this->getTotalItem() - $this->getTotalUsedItem());
    }

    /**
     * @return int
     */
    public function getMinStockCount()
    {
        return $this->minStockCount;
    }

    /**
     * @param int $minStockCount
     */
    public function setMinStockCount($minStockCount)
    {
        $this->minStockCount = $minStockCount;
    }

    /**
     * @return int
     */
    public function getOpenItem()
    {
        return $this->openItem;
    }

    /**
     * @param int $openItem
     */
    public function setOpenItem($openItem)
    {
        $this->openItem = $openItem;
    }

    /**
     * @return int
     */
    public function getMaxStockCount()
    {
        return $this->maxStockCount;
    }

    /**
     * @param int $maxStockCount
     */
    public function setMaxStockCount($maxStockCount)
    {
        $this->maxStockCount = $maxStockCount;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return \DateTime
     */
    public function getOpeningDate()
    {
        return $this->openingDate;
    }

    /**
     * @param \DateTime $openingDate
     */
    public function setOpeningDate($openingDate)
    {
        $this->openingDate = $openingDate;
    }

    /**
     * @return float
     */
    public function getExtraAddedItemQuantity()
    {
        return $this->extraAddedItemQuantity;
    }

    /**
     * @param float $extraAddedItemQuantity
     */
    public function setExtraAddedItemQuantity($extraAddedItemQuantity)
    {
        $this->extraAddedItemQuantity = $extraAddedItemQuantity;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return Project
     */
    public function getProjectTo()
    {
        return $this->projectTo;
    }

    /**
     * @param Project $projectTo
     */
    public function setProjectTo($projectTo)
    {
        $this->projectTo = $projectTo;
    }

    /**
     * @return float
     */
    public function getReceiveByProjectQty()
    {
        return $this->receiveByProjectQty;
    }

    /**
     * @param float $receiveByProjectQty
     */
    public function setReceiveByProjectQty($receiveByProjectQty)
    {
        $this->receiveByProjectQty = $receiveByProjectQty;
    }


}
