<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * ReadyForPayment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PaymentRepository")
 */
class Payment
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
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\RelatedBank", inversedBy="payment", cascade={"persist", "remove"})
     */
      private $relatedBank;

    /**
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", inversedBy="payment", cascade={"persist", "remove"})
     */
     private $purchaseOrder;

    /**
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Vendor", inversedBy="payments", cascade={"persist", "remove"})
     */
     private $vendor;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReadyForPayment", mappedBy="payment", cascade={"persist", "remove"})
     */
     private $readyForPayments;

    /**
     * @var float
     *
     * @ORM\Column(name="request_amount", type="float" , nullable = true)
     */
    private $requestAmount;

   /**
     * @var float
     *
     * @ORM\Column(name="advance_amount", type="float", nullable = true)
     */
    private $advanceAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="adjustment_amount", type="float", nullable = true)
     */
    private $adjustmentAmount;
    /**
     * @var float
     *
     * @ORM\Column(name="payment_amount", type="float",  nullable = true)
     */
    private $paymentAmount;
    /**
     * @var float
     *
     * @ORM\Column(name="due_amount", type="float", nullable = true)
     */
    private $dueAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="bill_number", type="string", nullable=true)
     */
    private $billNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="cheque", type="string", nullable=true)
     */
    private $cheque;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bill_date", type="datetime", nullable=true)
     */
    private $billDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="request_date", type="datetime", nullable=true)
     */
    private $requestDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $paymentBy;
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="payment_verified_by", nullable=true)
     */
    private $paymentVerifiedBy;


    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true )
     */
    private $status = 0;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->readyForPaymentDetails = new ArrayCollection();
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
     * @return mixed
     */
    public function getRelatedBank()
    {
        return $this->relatedBank;
    }

    /**
     * @param mixed $relatedBank
     */
    public function setRelatedBank($relatedBank)
    {
        $this->relatedBank = $relatedBank;
    }

    /**
     * @return float
     */
    public function getRequestAmount()
    {
        return $this->requestAmount;
    }

    /**
     * @param float $requestAmount
     */
    public function setRequestAmount($requestAmount)
    {
        $this->requestAmount = $requestAmount;
    }

    /**
     * @return float
     */
    public function getAdvanceAmount()
    {
        return $this->advanceAmount;
    }

    /**
     * @param float $advanceAmount
     */
    public function setAdvanceAmount($advanceAmount)
    {
        $this->advanceAmount = $advanceAmount;
    }

    /**
     * @return float
     */
    public function getAdjustmentAmount()
    {
        return $this->adjustmentAmount;
    }

    /**
     * @param float $adjustmentAmount
     */
    public function setAdjustmentAmount($adjustmentAmount)
    {
        $this->adjustmentAmount = $adjustmentAmount;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @param float $paymentAmount
     */
    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;
    }

    /**
     * @return float
     */
    public function getDueAmount()
    {
        return $this->dueAmount;
    }

    /**
     * @param float $dueAmount
     */
    public function setDueAmount($dueAmount)
    {
        $this->dueAmount = $dueAmount;
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
     * @return string
     */
    public function getCheque()
    {
        return $this->cheque;
    }

    /**
     * @param string $cheque
     */
    public function setCheque($cheque)
    {
        $this->cheque = $cheque;
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
     * @return \DateTime
     */
    public function getRequestDate()
    {
        return $this->requestDate;
    }

    /**
     * @param \DateTime $requestDate
     */
    public function setRequestDate($requestDate)
    {
        $this->requestDate = $requestDate;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return User
     */
    public function getPaymentBy()
    {
        return $this->paymentBy;
    }

    /**
     * @param User $paymentBy
     */
    public function setPaymentBy($paymentBy)
    {
        $this->paymentBy = $paymentBy;
    }

    /**
     * @return ArrayCollection
     */
    public function getReadyForPayments()
    {
        return $this->readyForPayments;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return User
     */
    public function getPaymentVerifiedBy()
    {
        return $this->paymentVerifiedBy;
    }

    /**
     * @param User $paymentVerifiedBy
     */
    public function setPaymentVerifiedBy($paymentVerifiedBy)
    {
        $this->paymentVerifiedBy = $paymentVerifiedBy;
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param mixed $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

}
