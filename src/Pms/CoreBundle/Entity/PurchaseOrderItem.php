<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Entity\Project;

/**
 * PurchaseOrderItem
 *
 * @ORM\Table(name="purchase_order_items")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseOrderItemRepository")
 */
class PurchaseOrderItem
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
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Item", inversedBy="purchaseOrderItem", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="items", nullable=true)
     */
    private $item;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="purchaseOrderItem", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="projects", nullable=true)
     */
    private $project;

    /**
     * @var float
     *
     * @ORM\Column(name="quantities", type="float", nullable=true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="total_order_receive_quantity", type="float", nullable=true)
     */
    private $totalOrderReceiveQuantity;

    /**
     * @var PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", inversedBy="purchaseOrderItems", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="purchase_orders", nullable=true)
     */
    private $purchaseOrder;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReceivedItem", mappedBy="purchaseOrderItem", cascade={"persist", "remove"})
     */
    private $receivedItems;

    /**
     * @var PurchaseRequisitionItem
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisitionItem", inversedBy="purchaseOrderItems", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="purchase_requisition_item", nullable=true)
     */
    private $purchaseRequisitionItem;

    /**
     * @ORM\OneToOne(targetEntity="PurchaseOrderItemClose", mappedBy="purchaseOrderItem", cascade={"persist"})
     *
     */
    protected $purchaseOrderItemClose;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="amendment", type="integer")
     */
    private $amendment;

    /**
     * @var float
     *
     * @ORM\Column(name="amendment_quantity", type="float", nullable=true)
     */
    private $amendmentQuantity;

    /**
     * @var integer
     *
     * @ORM\Column(name="amendment_ref", type="integer", nullable=true)
     */
    private $amendmentRef;

    /**
     * @var integer
     *
     * @ORM\Column(name="amendment_ref_po", type="integer", nullable=true)
     */
    private $amendmentRefPo;

    /**
     * @var integer
     *
     * @ORM\Column(name="approval_status", type="integer", nullable=true)
     */
    private $approvalStatus;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="brand", type="string", length=255, nullable=true)
     */
    private $brand;
    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="string", length=255, nullable=true)
     */
    private $remark;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount;

    public function __construct()
    {
        $this->receivedItems = new ArrayCollection();
    }

    public function addReceivedItem(ReceivedItem $item)
    {
        $item->setPurchaseOrderItem($this);

        if (!$this->getReceivedItems()->contains($item)) {
            $this->receivedItems->add($item);
        }

        return $this;
    }

    public function removeReceivedItem(ReceivedItem $item)
    {
        if ($this->getReceivedItems()->contains($item)) {
            $this->getReceivedItems()->removeElement($item);
        }
    }

    public function getReceivedItems()
    {
        return $this->receivedItems;
    }

    /**
     * Set totalOrderReceiveQuantity
     *
     * @param float $totalOrderReceiveQuantity
     * @return PurchaseOrderItem
     */
    public function setTotalOrderReceiveQuantity($totalOrderReceiveQuantity)
    {
        $this->totalOrderReceiveQuantity = $totalOrderReceiveQuantity;

        return $this;
    }

    /**
     * Get totalOrderReceiveQuantity
     *
     * @return float
     */
    public function getTotalOrderReceiveQuantity()
    {
        return $this->totalOrderReceiveQuantity;
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
     * Set quantity
     *
     * @param float $quantity
     * @return PurchaseOrderItem
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
     * Set purchaseOrder
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrderItem
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Get purchaseOrder
     *
     * @return PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return PurchaseOrderItem
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
     * Set status
     *
     * @param integer $status
     * @return PurchaseOrderItem
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
     * Set amendment
     *
     * @param integer $amendment
     * @return PurchaseOrderItem
     */
    public function setAmendment($amendment)
    {
        $this->amendment = $amendment;

        return $this;
    }

    /**
     * Get amendment
     *
     * @return integer
     */
    public function getAmendment()
    {
        return $this->amendment;
    }

    /**
     * Set amendmentQuantity
     *
     * @param float $amendmentQuantity
     * @return PurchaseOrderItem
     */
    public function setAmendmentQuantity($amendmentQuantity)
    {
        $this->amendmentQuantity = $amendmentQuantity;

        return $this;
    }

    /**
     * Get amendmentQuantity
     *
     * @return float
     */
    public function getAmendmentQuantity()
    {
        return $this->amendmentQuantity;
    }

    /**
     * Set amendmentRef
     *
     * @param integer $amendmentRef
     * @return PurchaseOrderItem
     */
    public function setAmendmentRef($amendmentRef)
    {
        $this->amendmentRef = $amendmentRef;

        return $this;
    }

    /**
     * Get amendmentRef
     *
     * @return integer
     */
    public function getAmendmentRef()
    {
        return $this->amendmentRef;
    }

    /**
     * Set amendmentRefPo
     *
     * @param integer $amendmentRefPo
     * @return PurchaseOrderItem
     */
    public function setAmendmentRefPo($amendmentRefPo)
    {
        $this->amendmentRefPo = $amendmentRefPo;

        return $this;
    }

    /**
     * Get amendmentRefPo
     *
     * @return integer
     */
    public function getAmendmentRefPo()
    {
        return $this->amendmentRefPo;
    }

    /**
     * Set approvalStatus
     *
     * @param integer $approvalStatus
     * @return PurchaseOrderItem
     */
    public function setApprovalStatus($approvalStatus)
    {
        $this->approvalStatus = $approvalStatus;

        return $this;
    }

    /**
     * Get approvalStatus
     *
     * @return integer
     */
    public function getApprovalStatus()
    {
        return $this->approvalStatus;
    }

    /**
     * Set project
     *
     * @param Project $project
     * @return PurchaseOrderItem
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
     * @return \Pms\CoreBundle\Entity\PurchaseRequisitionItem
     */
    public function getPurchaseRequisitionItem()
    {
        return $this->purchaseRequisitionItem;
    }

    /**
     * @param \Pms\CoreBundle\Entity\PurchaseRequisitionItem $purchaseRequisitionItem
     * @return $this
     */
    public function setPurchaseRequisitionItem($purchaseRequisitionItem)
    {
        $this->purchaseRequisitionItem = $purchaseRequisitionItem;
        $this->item = $purchaseRequisitionItem->getItem();
        $this->project = $purchaseRequisitionItem->getPurchaseRequisition()->getProject();
        return $this;
    }

    public function getItemName()
    {
        $this->item->getItemName();
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
     * Set price
     *
     * @param float $price
     * @return PurchaseOrderItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
     * Set amount
     *
     * @param float $amount
     * @return PurchaseOrderItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /*
        public function receiveItem($quantity) {
            $this->remainingQuantity -= $quantity;
        }*/
}
