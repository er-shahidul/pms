<?php

namespace Pms\CoreBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Pms\CoreBundle\Entity\PurchaseOrder;
use Pms\UserBundle\Entity\User;

/**
 * PoLog
 *
 * @ORM\Table(name="po_log")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\Log\PoLogRepository")
 */
class PoLog
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
     * @var PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", inversedBy="poLogs")
     * @ORM\JoinColumn(name="purchase_order")
     */
    private $purchaseOrder;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by")
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

    public function __construct()
    {
        $this->createdDate = new \DateTime();
    }

    /**
     * Set purchaseOrder
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PoLog
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
     * Set createdBy
     *
     * @param User $createdBy
     * @return PoLog
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
     * @return PoLog
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
     * @return PoLog
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
