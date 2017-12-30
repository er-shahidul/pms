<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * PurchaseOrderItemClose
 *
 * @ORM\Table(name="purchase_order_items_close")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseOrderItemCloseRepository")
 */
class PurchaseOrderItemClose
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
     * @ORM\OneToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderItem", inversedBy="purchaseOrderItemClose")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchaseOrderItem_id", referencedColumnName="id", unique=true, onDelete="CASCADE")
     * })
     */
    protected $purchaseOrderItem;

    /**
     * Set quantity
     *
     * @param float $quantity
     * @return PurchaseOrderItemClose
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
     * @return PurchaseOrderItemClose
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
     * @return PurchaseOrderItemClose
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
     * Set purchaseOrderItem
     *
     * @param PurchaseOrderItem $purchaseOrderItem
     * @return PurchaseOrderItemClose
     */
    public function setPurchaseOrderItem(PurchaseOrderItem $purchaseOrderItem = null)
    {
        $this->purchaseOrderItem = $purchaseOrderItem;

        return $this;
    }

    /**
     * Get purchaseOrderItem
     *
     * @return PurchaseOrderItem
     */
    public function getPurchaseOrderItem()
    {
        return $this->purchaseOrderItem;
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
