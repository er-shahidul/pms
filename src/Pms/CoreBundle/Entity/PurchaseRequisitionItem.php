<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Item;
use Pms\UserBundle\Entity\User;

/**
 * PurchaseRequisitionItem
 *
 * @ORM\Table(name="purchase_requisition_items")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseRequisitionItemRepository")
 */
class PurchaseRequisitionItem
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
     * @var float
     *
     * @ORM\Column(name="cost", type="float" , nullable=true)
     */
    private $totalPrice;

    /**
     * @var PurchaseRequisition
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisition", inversedBy="purchaseRequisitionItems", cascade={"persist"})
     * @ORM\JoinColumn(name="purchase_requisitions", nullable=true)
     */
    private $purchaseRequisition;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Item", inversedBy="purchaseRequisitionItem", cascade={"persist"})
     * @ORM\JoinColumn(name="items", nullable=true)
     */
    private $item;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderItem", mappedBy="purchaseRequisitionItem", cascade={"persist", "remove"})
     */
    private $purchaseOrderItems;

    /**
     * @var float
     *
     * @ORM\Column(name="quantities", type="float", nullable=true)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", nullable=true)
     */
    private $comment;
    /**
     * @var string
     *
     * @ORM\Column(name="close_remark", type="string", nullable=true)
     */
    private $closeRemark;

    /**
     * @var /DateTime
     *
     * @ORM\Column(name="date_of_required", type="date", nullable=true)
     */
    private $dateOfRequired;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var float
     *
     * @ORM\Column(name="purchase_order_quantity", type="float", nullable=true)
     */
    private $purchaseOrderQuantity;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="claimed_by", nullable=true)
     */
    private $claimedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="claimed_date", type="datetime", nullable=true)
     */
    private $claimedDate;

    /**
     * @ORM\OneToOne(targetEntity="PurchaseRequisitionItemCloseInfo", mappedBy="purchaseRequisitionItem", cascade={"persist"})
     *
     */
    protected $purchaseRequisitionItemCloseInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="is_head_or_local", type="string", length=255, nullable=true)
     */
    private $isHeadOrLocal;

    /**
     * @return string
     */
    public function getIsHeadOrLocal()
    {
        return $this->isHeadOrLocal;
    }

    /**
     * @param string $isHeadOrLocal
     */
    public function setIsHeadOrLocal($isHeadOrLocal)
    {
        $this->isHeadOrLocal = $isHeadOrLocal;
    }

    /**
     * @param mixed $purchaseRequisitionItemCloseInfo
     */
    public function setPurchaseRequisitionItemCloseInfo($purchaseRequisitionItemCloseInfo)
    {
        $purchaseRequisitionItemCloseInfo->setPurchaseRequisitionItem($this);
        $this->purchaseRequisitionItemCloseInfo = $purchaseRequisitionItemCloseInfo;
    }

    /**
     * @return mixed
     */
    public function getPurchaseRequisitionItemCloseInfo()
    {
        return $this->purchaseRequisitionItemCloseInfo;
    }

    /**
     * Set claimedBy
     *
     * @param User $claimedBy
     * @return PurchaseRequisitionItem
     */
    public function setClaimedBy($claimedBy)
    {
        $this->claimedBy = $claimedBy;

        return $this;
    }

    /**
     * Get claimedBy
     *
     * @return User
     */
    public function getClaimedBy()
    {
        return $this->claimedBy;
    }

    /**
     * Set claimedDate
     *
     * @param \DateTime $claimedDate
     * @return PurchaseRequisition
     */
    public function setClaimedDate($claimedDate)
    {
        $this->claimedDate = $claimedDate;

        return $this;
    }

    /**
     * Get claimedDate
     *
     * @return \DateTime
     */
    public function getClaimedDate()
    {
        return $this->claimedDate;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="po_approval_status", type="integer", nullable=true)
     */
    private $poApprovalStatus;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseOrderItems()
    {
        return $this->purchaseOrderItems;
    }

    public function __toString()
    {
        return $this->getId() . "";
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
     * Set item
     *
     * @param Item $item
     * @return PurchaseRequisitionItem
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
     * Set purchaseRequisition
     *
     * @param PurchaseRequisition $purchaseRequisition
     * @return PurchaseRequisitionItem
     */
    public function setPurchaseRequisition($purchaseRequisition)
    {
        $this->purchaseRequisition = $purchaseRequisition;

        return $this;
    }

    /**
     * Get purchaseRequisition
     *
     * @return PurchaseRequisition
     */
    public function getPurchaseRequisition()
    {
        return $this->purchaseRequisition;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return PurchaseRequisitionItem
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
     * Set totalPrice
     *
     * @param float $totalPrice
     * @return PurchaseRequisitionItem
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set dateOfRequired
     *
     * @param /DateTime $dateOfRequired
     * @return PurchaseRequisitionItem
     */
    public function setDateOfRequired($dateOfRequired)
    {
        $this->dateOfRequired = $dateOfRequired;

        return $this;
    }

    /**
     * Get dateOfRequired
     *
     * @return /DateTime
     */
    public function getDateOfRequired()
    {
        return $this->dateOfRequired;
    }

    public function getDateOfRequiredText() {
        if(empty($this->dateOfRequired)){
            return "";
        }

        return $this->getDateOfRequired()->format('Y-m-d');
    }

    public function setDateOfRequiredText($date = "") {

        if(!empty($date)){
            return $this->setDateOfRequired(new \DateTime($date));
        }

        return $this;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return PurchaseRequisitionItem
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
     * Set status
     *
     * @param integer $status
     * @return PurchaseRequisitionItem
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
     * Set poApprovalStatus
     *
     * @param integer $poApprovalStatus
     * @return PurchaseRequisition
     */
    public function setPoApprovalStatus($poApprovalStatus)
    {
        $this->poApprovalStatus = $poApprovalStatus;

        return $this;
    }

    /**
     * Get poApprovalStatus
     *
     * @return integer
     */
    public function getPoApprovalStatus()
    {
        return $this->poApprovalStatus;
    }

    /**
     * Set purchaseOrderQuantity
     *
     * @param float $purchaseOrderQuantity
     * @return PurchaseRequisitionItem
     */
    public function setPurchaseOrderQuantity($purchaseOrderQuantity)
    {
        $this->purchaseOrderQuantity = $purchaseOrderQuantity;

        return $this;
    }

    /**
     * Get purchaseOrderQuantity
     *
     * @return float
     */
    public function getPurchaseOrderQuantity()
    {
        return $this->purchaseOrderQuantity;
    }

    public function getItemName()
    {
        $this->getItem()->getItemName();
    }

    public function getPoDate()
    {
        if($this->getPurchaseOrderItems()->count() > 0) {
            $purchaseOrderDate = $this->getPurchaseOrderItems()->get(0);
            return $purchaseOrderDate->getPurchaseOrder()->getCreatedDate()->format('Y-m-d');
        }
    }

    /**
     * @return string
     */
    public function getCloseRemark()
    {
        return $this->closeRemark;
    }

    /**
     * @param string $closeRemark
     */
    public function setCloseRemark($closeRemark)
    {
        $this->closeRemark = $closeRemark;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}