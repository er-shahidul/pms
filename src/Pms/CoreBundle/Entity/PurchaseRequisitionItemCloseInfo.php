<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * PurchaseRequisitionItemCloseInfo
 *
 * @ORM\Table(name="purchase_requisition_items_close_info")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseRequisitionItemCloseInfoRepository")
 */
class PurchaseRequisitionItemCloseInfo
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="closed_by", nullable=true)
     */
    private $closedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_date", type="datetime", nullable=true)
     */
    private $closedDate;

    /**
     * @var float
     *
     * @ORM\Column(name="quantities", type="float", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\OneToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisitionItem", inversedBy="purchaseRequisitionItemCloseInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchaseRequisitionItem_id", referencedColumnName="id", unique=true, onDelete="CASCADE")
     * })
     */
    protected $purchaseRequisitionItem;

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return PurchaseRequisitionItemCloseInfo
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
     * Set closedBy
     *
     * @param User $closedBy
     * @return PurchaseRequisitionItemCloseInfo
     */
    public function setClosedBy($closedBy)
    {
        $this->closedBy = $closedBy;

        return $this;
    }

    /**
     * Get closedBy
     *
     * @return User
     */
    public function getClosedBy()
    {
        return $this->closedBy;
    }

    /**
     * Set closedDate
     *
     * @param \DateTime $closedDate
     * @return PurchaseRequisitionItemCloseInfo
     */
    public function setClosedDate($closedDate)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Get closedDate
     *
     * @return \DateTime
     */
    public function getClosedDate()
    {
        return $this->closedDate;
    }

    /**
     * Set purchaseRequisitionItem
     *
     * @param PurchaseRequisitionItem $purchaseRequisitionItem
     * @return PurchaseRequisitionItemCloseInfo
     */
    public function setPurchaseRequisitionItem(PurchaseRequisitionItem $purchaseRequisitionItem = null)
    {
        $this->purchaseRequisitionItem = $purchaseRequisitionItem;

        return $this;
    }

    /**
     * Get purchaseRequisitionItem
     *
     * @return PurchaseRequisitionItem
     */
    public function getPurchaseRequisitionItem()
    {
        return $this->purchaseRequisitionItem;
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
}
