<?php

namespace Pms\ReportBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * PriceDirectoryFromOld
 *
 * @ORM\Table(name="price_directory_from_old")
 * @ORM\Entity(repositoryClass="Pms\ReportBundle\Entity\Repository\PriceDirectoryFromOldRepository")
 */
class PriceDirectoryFromOld
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
     * @var string
     *
     * @ORM\Column(name="categoryName", type="string", nullable=true)
     */
    private $categoryName;
    /**
     * @var string
     *
     * @ORM\Column(name="approveStatus", type="string", nullable=true)
     */
    private $approveStatus;
    /**
     * @var string
     *
     * @ORM\Column(name="itemType", type="string", nullable=true)
     */
    private $itemType;
    /**
     * @var string
     *
     * @ORM\Column(name="itemName", type="string", nullable=true)
     */
    private $itemName;
    /**
     * @var string
     *
     * @ORM\Column(name="itemUnit", type="string", nullable=true)
     */
    private $itemUnit;
    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", nullable=true)
     */
    private $price;
    /**
     * @var string
     *
     * @ORM\Column(name="brand", type="string", nullable=true)
     */
    private $brand;
    /**
     * @var DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime", nullable=true)
     */
    private $createdDate;
    /**
     * @var string
     *
     * @ORM\Column(name="areaName", type="string", nullable=true)
     */
    private $areaName;
    /**
     * @var string
     *
     * @ORM\Column(name="projectName", type="string", nullable=true)
     */
    private $projectName;
    /**
     * @var string
     *
     * @ORM\Column(name="vendorName", type="string", nullable=true)
     */
    private $vendorName;
    /**
     * @var string
     *
     * @ORM\Column(name="vendorAddress", type="string", nullable=true)
     */
    private $vendorAddress;
    /**
     * @var string
     *
     * @ORM\Column(name="contractPerson", type="string", nullable=true)
     */
    private $contractPerson;
    /**
     * @var string
     *
     * @ORM\Column(name="refNo", type="string", nullable=true)
     */
    private $refNo;
    /**
     * @var string
     *
     * @ORM\Column(name="totalOrderItemQuantity", type="string", nullable=true)
     */
    private $totalOrderItemQuantity;
    /**
     * @var string
     *
     * @ORM\Column(name="buyer", type="string", nullable=true)
     */
    private $buyer;
    /**
     * @var string
     *
     * @ORM\Column(name="poNonpo", type="string", nullable=true)
     */
    private $poNonpo;

    /**
     * @return string
     */
    public function getPoNonpo()
    {
        return $this->poNonpo;
    }

    /**
     * @return string
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @param string $buyer
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;
    }

    /**
     * @param string $poNonpo
     */
    public function setPoNonpo($poNonpo)
    {
        $this->poNonpo = $poNonpo;
    }

    /**
     * @return string
     */
    public function getTotalOrderItemQuantity()
    {
        return $this->totalOrderItemQuantity;
    }

    /**
     * @param string $totalOrderItemQuantity
     */
    public function setTotalOrderItemQuantity($totalOrderItemQuantity)
    {
        $this->totalOrderItemQuantity = $totalOrderItemQuantity;
    }

    /**
     * @return string
     */
    public function getRefNo()
    {
        return $this->refNo;
    }

    /**
     * @param string $refNo
     */
    public function setRefNo($refNo)
    {
        $this->refNo = $refNo;
    }

    /**
     * @return string
     */
    public function getContractPerson()
    {
        return $this->contractPerson;
    }

    /**
     * @param string $contractPerson
     */
    public function setContractPerson($contractPerson)
    {
        $this->contractPerson = $contractPerson;
    }

    /**
     * @return string
     */
    public function getVendorAddress()
    {
        return $this->vendorAddress;
    }

    /**
     * @param string $vendorAddress
     */
    public function setVendorAddress($vendorAddress)
    {
        $this->vendorAddress = $vendorAddress;
    }

    /**
     * @return string
     */
    public function getVendorName()
    {
        return $this->vendorName;
    }

    /**
     * @param string $vendorName
     */
    public function setVendorName($vendorName)
    {
        $this->vendorName = $vendorName;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @param string $projectName
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }

    /**
     * @return string
     */
    public function getAreaName()
    {
        return $this->areaName;
    }

    /**
     * @param string $areaName
     */
    public function setAreaName($areaName)
    {
        $this->areaName = $areaName;
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
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getItemUnit()
    {
        return $this->itemUnit;
    }

    /**
     * @param string $itemUnit
     */
    public function setItemUnit($itemUnit)
    {
        $this->itemUnit = $itemUnit;
    }

    /**
     * @return string
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    /**
     * @param string $itemName
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param string $itemType
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
    }

    /**
     * @return string
     */
    public function getApproveStatus()
    {
        return $this->approveStatus;
    }

    /**
     * @param string $approveStatus
     */
    public function setApproveStatus($approveStatus)
    {
        $this->approveStatus = $approveStatus;
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
     * @return DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param DateTime $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }
}
