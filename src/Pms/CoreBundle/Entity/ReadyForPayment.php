<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * ReadyForPayment
 *
 * @ORM\Table(name="ready_for_payments")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\ReadyForPaymentRepository")
 */
class ReadyForPayment
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
     * @var Receive
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\Receive", inversedBy="readyForPayments")
     * @ORM\JoinColumn(name="grn", nullable=true)
     */
     private $grn;

    /**
     * @var PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", inversedBy="readyForPayments")
     * @ORM\JoinColumn(name="purchaseOrders")
     */
     private $purchaseOrder;

    /**
     * @var Payment
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\Payment", inversedBy="readyForPayments")
     * @ORM\JoinColumn(name="payment_id")
     */
     private $payment;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="float", nullable = true)
     */
     private $amount;
    /**
     * @var integer
     *
     * @ORM\Column(name="verified_status", type="integer", nullable = true)
     */
     private $verifiedStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
     private $created;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bill_date", type="datetime", nullable=true)
     */
     private $billDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="verified_by", nullable=true)
     */
    private $verifiedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="bill_number", type="string", nullable=true)
     */
    private $billNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="send_back_status", type="integer", nullable=true)
     */
    private $sendBackStatus;
    /**
     * @var string
     *
     * @ORM\Column(name="send_back_comment", type="string", nullable=true)
     */
    private $sendBackComments;

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
     * Get grn
     *
     * @return Receive
     */
    public function getGrn()
    {
        return $this->grn;
    }

    /**
     * Set grn
     *
     * @param Receive $grn
     * @return ReadyForPayment
     */
    public function setGrn($grn)
    {
        $this->grn = $grn;
    }


    /**
     * @return mixed
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * @param mixed $purchaseOrder
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param mixed $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return int
     */
    public function getVerifiedStatus()
    {
        return $this->verifiedStatus;
    }

    /**
     * @param int $verifiedStatus
     */
    public function setVerifiedStatus($verifiedStatus)
    {
        $this->verifiedStatus = $verifiedStatus;
    }

    /**
     * @return User
     */
    public function getVerifiedBy()
    {
        return $this->verifiedBy;
    }

    /**
     * @param User $verifiedBy
     */
    public function setVerifiedBy($verifiedBy)
    {
        $this->verifiedBy = $verifiedBy;
    }

    /**
     * @return string
     */
    public function getBillNumber()
    {
        return $this->billNumber;
    }

    /**
     * @param string $billNumber
     */
    public function setBillNumber($billNumber)
    {
        $this->billNumber = $billNumber;
    }

    /**
     * @return \DateTime
     */
    public function getBillDate()
    {
        return $this->billDate;
    }

    /**
     * @param \DateTime $billDate
     */
    public function setBillDate($billDate)
    {
        $this->billDate = $billDate;
    }

    /**
     * @return string
     */
    public function getSendBackStatus()
    {
        return $this->sendBackStatus;
    }

    /**
     * @param string $sendBackStatus
     */
    public function setSendBackStatus($sendBackStatus)
    {
        $this->sendBackStatus = $sendBackStatus;
    }

    /**
     * @return string
     */
    public function getSendBackComments()
    {
        return $this->sendBackComments;
    }

    /**
     * @param string $sendBackComments
     */
    public function setSendBackComments($sendBackComments)
    {
        $this->sendBackComments = $sendBackComments;
    }


}
