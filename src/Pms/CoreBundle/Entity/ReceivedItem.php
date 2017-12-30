<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Entity\Project;

/**
 * ReceivedItem
 *
 * @ORM\Table(name="received_items")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\ReceivedItemRepository")
 */
class ReceivedItem
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
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Item", inversedBy="receivedItem", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="items", nullable=true)
     */
    private $item;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="receivedItem", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="projects", nullable=true)
     */
    private $project;

    /**
     * @var Receive
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\Receive", inversedBy="receiveItems", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="receives", nullable=true)
     */
    private $receive;

    /**
     * @var float
     *
     * @ORM\Column(name="quantities", type="float")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", nullable=true)
     */
    private $comment;

    /**
     * @var PurchaseOrderItem
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderItem", inversedBy="receivedItems", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="purchase_order_items", nullable=true)
     */
    private $purchaseOrderItem;

    /**
     * @return string
     */
    public function getPr()
    {
        $pr = $this->getPurchaseOrderItem()->getPurchaseRequisitionItem()->getPurchaseRequisition();

        return $pr;
    }

    /**
     * @return string
     */
    public function getPo()
    {
        $po = $this->getPurchaseOrderItem()->getPurchaseOrder();

        return $po;
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
     * Set comment
     *
     * @param string $comment
     * @return ReceivedItem
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return ReceivedItem
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
     * @param \Pms\CoreBundle\Entity\PurchaseOrderItem $purchaseOrderItem
     * @return $this
     */
    public function setPurchaseOrderItem($purchaseOrderItem)
    {
        $this->purchaseOrderItem = $purchaseOrderItem;
        $this->item = $purchaseOrderItem->getItem();
        $this->project = $purchaseOrderItem->getProject();
        return $this;
    }

    /**
     * @return \Pms\CoreBundle\Entity\PurchaseOrderItem
     */
    public function getPurchaseOrderItem()
    {
        return $this->purchaseOrderItem;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return ReceivedItem
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set project
     *
     * @param Project $project
     * @return ReceivedItem
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set receive
     *
     * @param Receive $receive
     * @return ReceivedItem
     */
    public function setReceive($receive)
    {
        $this->receive = $receive;

        return $this;
    }

    /**
     * Get receive
     *
     * @return Receive
     */
    public function getReceive()
    {
        return $this->receive;
    }
}
