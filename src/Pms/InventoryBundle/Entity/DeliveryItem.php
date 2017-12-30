<?php

namespace Pms\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Entity\Project;

/**
 * DeliveryItem
 *
 * @ORM\Table(name="delivery_items")
 * @ORM\Entity(repositoryClass="Pms\InventoryBundle\Entity\Repository\DeliveryItemRepository")
 */
class DeliveryItem
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
     * @var Delivery
     *
     * @ORM\ManyToOne(targetEntity="Pms\InventoryBundle\Entity\Delivery", inversedBy="deliveryItem",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="delivery_id")
     */
    private $delivery;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Item", inversedBy="deliveryItem")
     * @ORM\JoinColumn(name="item_id", nullable=true)
     */
    private $item;


    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @return float
     */
    public function getAvgPrice()
    {
        return $this->avgPrice;
    }

    /**
     * @param float $avgPrice
     */
    public function setAvgPrice($avgPrice)
    {
        $this->avgPrice = $avgPrice;
    }
    
    /**
     * @var float
     */
    private $avgPrice;


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
     * Set quantity
     *
     * @param float $quantity
     * @return DeliveryItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return Project
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param Project $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return Project
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Project $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }
}
