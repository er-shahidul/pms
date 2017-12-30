<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * Vendor
 *
 * @ORM\Table(name="vendors")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\VendorRepository")
 */
class Vendor
{
    const VAT = 1;
    const TAX = 2;
    const TRADE = 3;
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\SettingBundle\Entity\VendorAttach", mappedBy="vendor", cascade={"persist", "remove"})
     */
    private $vendorAttach;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", mappedBy="vendor")
     */
    private $purchaseOrder;

     /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Payment", mappedBy="vendor")
     */
    private $payments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Receive", mappedBy="vendor")
     */
    private $receive;

    /**
     * @var string
     *
     * @ORM\Column(name="vendors_name", type="string", length=255)
     */
    private $vendorName;

    /**
     * @var text
     *
     * @ORM\Column(name="vendors_address", type="text")
     */
    private $vendorAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_person", type="string", length=255)
     */
    private $contractPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_no", type="string", length=255)
     */
    private $contractNo;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="trade_license_no", type="string", length=255)
     */
    private $tradeLicenseNo;

    /**
     * @var string
     *
     * @ORM\Column(name="tin_certificate_no", type="string", length=255)
     */
    private $tinCertificateNo;

    /**
     * @var string
     *
     * @ORM\Column(name="vat_certificate_no", type="string", length=255)
     */
    private $vatCertificateNo;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_no", type="string", length=255)
     */
    private $bankAccountNo;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_name", type="string", length=255)
     */
    private $bankAccountName;

    /**
     * @var string
     *
     * @ORM\Column(name="branch_name", type="string", length=255)
     */
    private $branchName;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", length=255)
     */
    private $PaymentType;

    /**
     * @var Area
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Area", inversedBy="vendor", cascade={"persist"})
     */
    protected $area;

    /**
     * @var ItemType
     *
     * @ORM\ManyToMany(targetEntity="Pms\SettingBundle\Entity\ItemType", inversedBy="vendors", cascade={"persist"})
     */
    protected $itemTypes;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", nullable=true)
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
        $this->vendorAttach = new ArrayCollection();
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getVendorAttach()
    {
        return $this->vendorAttach;
    }

    /**
     * Set vendorName
     *
     * @param string $vendorName
     * @return Vendor
     */
    public function setVendorName($vendorName)
    {
        $this->vendorName = $vendorName;

        return $this;
    }

    /**
     * Get vendorName
     *
     * @return string
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * Set contractPerson
     *
     * @param string $contractPerson
     * @return Vendor
     */
    public function setContractPerson($contractPerson)
    {
        $this->contractPerson = $contractPerson;

        return $this;
    }

    /**
     * Get contractPerson
     *
     * @return string
     */
    public function getContractPerson()
    {
        return $this->contractPerson;
    }

    /**
     * Set contractNo
     *
     * @param string $contractNo
     * @return Vendor
     */
    public function setContractNo($contractNo)
    {
        $this->contractNo = $contractNo;

        return $this;
    }

    /**
     * Get contractNo
     *
     * @return string
     */
    public function getContractNo()
    {
        return $this->contractNo;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Vendor
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set tradeLicenseNo
     *
     * @param string $tradeLicenseNo
     * @return Vendor
     */
    public function setTradeLicenseNo($tradeLicenseNo)
    {
        $this->tradeLicenseNo = $tradeLicenseNo;

        return $this;
    }

    /**
     * Get tradeLicenseNo
     *
     * @return string
     */
    public function getTradeLicenseNo()
    {
        return $this->tradeLicenseNo;
    }

    /**
     * Set tinCertificateNo
     *
     * @param string $tinCertificateNo
     * @return Vendor
     */
    public function setTinCertificateNo($tinCertificateNo)
    {
        $this->tinCertificateNo = $tinCertificateNo;

        return $this;
    }

    /**
     * Get tinCertificateNo
     *
     * @return string
     */
    public function getTinCertificateNo()
    {
        return $this->tinCertificateNo;
    }

    /**
     * Set vatCertificateNo
     *
     * @param string $vatCertificateNo
     * @return Vendor
     */
    public function setVatCertificateNo($vatCertificateNo)
    {
        $this->vatCertificateNo = $vatCertificateNo;

        return $this;
    }

    /**
     * Get vatCertificateNo
     *
     * @return string
     */
    public function getVatCertificateNo()
    {
        return $this->vatCertificateNo;
    }

    /**
     * Set bankAccountNo
     *
     * @param string $bankAccountNo
     * @return Vendor
     */
    public function setBankAccountNo($bankAccountNo)
    {
        $this->bankAccountNo = $bankAccountNo;

        return $this;
    }

    /**
     * Get bankAccountNo
     *
     * @return string
     */
    public function getBankAccountNo()
    {
        return $this->bankAccountNo;
    }

    /**
     * Set bankAccountName
     *
     * @param string $bankAccountName
     * @return Vendor
     */
    public function setBankAccountName($bankAccountName)
    {
        $this->bankAccountName = $bankAccountName;

        return $this;
    }

    /**
     * Get bankAccountName
     *
     * @return string
     */
    public function getBankAccountName()
    {
        return $this->bankAccountName;
    }

    /**
     * Set branchName
     *
     * @param string $branchName
     * @return Vendor
     */
    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;

        return $this;
    }

    /**
     * Get branchName
     *
     * @return string
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * Get vendorAddress
     * @return string
     */
    public function getVendorAddress()
    {
        return $this->vendorAddress;
    }

    /**
     * Set vendorAddress
     *
     * @param string $vendorAddress
     * @return Vendor
     */
    public function setVendorAddress($vendorAddress)
    {
        $this->vendorAddress = $vendorAddress;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return Vendor
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
     * @return Vendor
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
     * @return Vendor
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReceive()
    {
        return $this->receive;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->PaymentType;
    }

    /**
     * @param string $PaymentType
     */
    public function setPaymentType($PaymentType)
    {
        $this->PaymentType = $PaymentType;
    }

    /**
     * Get area
     *
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set area
     *
     * @param Area $area
     * @return Vendor
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return Area
     */
    public function getItemTypes()
    {
        return $this->itemTypes;
    }

    /**
     * @param Area $itemTypes
     */
    public function setItemTypes($itemTypes)
    {
        $this->itemTypes = $itemTypes;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function setVatFile(VendorAttach $vat) {
        $this->getVendorAttach()->add($vat);
        $vat->setVendor($this);
    }

    public function setTaxFile(VendorAttach $tax) {
        $this->getVendorAttach()->add($tax);
        $tax->setVendor($this);
    }

    public function setTradeFile(VendorAttach $trade) {
        $this->getVendorAttach()->add($trade);
        $trade->setVendor($this);
    }

    public function getVatFile() {
        return $this->getAttachmentByType(self::VAT);
    }

    public function getTaxFile() {
        return $this->getAttachmentByType(self::TAX);
    }

    public function getTradeFile() {
        return $this->getAttachmentByType(self::TRADE);
    }

    /**
     * @param $type
     * @return mixed|null
     */
    private function getAttachmentByType($type)
    {
        if($this->getVendorAttach() == null) {
            return null;
        }

        foreach ($this->getVendorAttach() as $attach) {
            if ($attach->getFileType() == $type) {
                return $attach;
            }
        }
        return null;
    }
}
