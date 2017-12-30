<?php

namespace Pms\DocumentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\CoreBundle\Entity\PurchaseOrder;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="documents")
 * @ORM\Entity(repositoryClass="Pms\DocumentBundle\Entity\Repository\DocumentRepository")
 * @ORM\HasLifecycleCallbacks
*/
class Document
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Receive", mappedBy="invoice", cascade={"persist", "remove"})
     */
    private $receiveInvoice;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Receive", mappedBy="calan", cascade={"persist", "remove"})
     */
    private $receiveChallan;


    /**
     * @var PurchaseOrder
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", inversedBy="document")
     * @ORM\JoinColumn(name="purchase_order", nullable=true)
     */
    private $purchaseOrder;


    /**
     * @ORM\Column(name="titles", type="string", length=255, nullable=true)
     */

    private $title;
    /**
     * @ORM\Column(name="descriptions", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(name="bill_number", type="string", nullable=true)
     */
    private $billNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="bill_amount", type="float", nullable = true)
     */
    private $billAmount;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $file;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="uploaded_by", nullable=true)
     */
    private $uploadedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uploaded_date", type="datetime")
     */
    private $uploadedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bill_date", type="datetime")
     */
    private $dateOfBill;

    public $temp;

    /**
     * @var integer
     *
     * @ORM\Column(name="invoice_or_calan", type="integer")
     */
    private $invoiceOrCalan;

    public function getAbsolutePathByIndex($index) {
        switch($index) {
            case 1: return $this->getAbsolutePath();
        }
    }

    public function getWebPathByIndex($index) {
        switch($index) {
            case 1: return $this->getWebPath();
        }
    }

    /**
     * Set invoiceOrCalan
     *
     * @param integer $invoiceOrCalan
     * @return Document
     */
    public function setInvoiceOrCalan($invoiceOrCalan)
    {
        $this->invoiceOrCalan = $invoiceOrCalan;

        return $this;
    }

    /**
     * Get invoiceOrCalan
     *
     * @return integer
     */
    public function getInvoiceOrCalan()
    {
        return $this->invoiceOrCalan;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReceiveInvoice()
    {
        return $this->receiveInvoice;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReceiveChallan()
    {
        return $this->receiveChallan;
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
     * Set uploadedBy
     *
     * @param User $uploadedBy
     * @return Document
     */
    public function setUploadedBy($uploadedBy)
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    /**
     * Get uploadedBy
     *
     * @return User
     */
    public function getUploadedBy()
    {
        return $this->uploadedBy;
    }

    /**
     * Set uploadedDate
     *
     * @param \DateTime $uploadedDate
     * @return Document
     */
    public function setUploadedDate($uploadedDate)
    {
        $this->uploadedDate = $uploadedDate;

        return $this;
    }

    /**
     * Get uploadedDate
     *
     * @return \DateTime
     */
    public function getUploadedDate()
    {
        return $this->uploadedDate;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename . '.' . $this->getFile()->guessExtension();
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir() . '/' . $this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/file';
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * @return PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
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
     * @return int
     */
    public function getBillAmount()
    {
        return $this->billAmount;
    }

    /**
     * @param int $billAmount
     */
    public function setBillAmount($billAmount)
    {
        $this->billAmount = $billAmount;
    }



    /**
     * Set dateOfBill
     *
     * @param /DateTime $dateOfBill
     * @return Document
     */
    public function setDateOfDelivered($dateOfBill)
    {
        $this->dateOfBill = $dateOfBill;

        return $this;
    }

    /**
     * Get dateOfBill
     *
     * @return /DateTime
     */
    public function getDateOfDelivered()
    {
        return $this->dateOfBill;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfBill()
    {
        return $this->dateOfBill;
    }

    /**
     * @param \DateTime $dateOfBill
     */
    public function setDateOfBill($dateOfBill)
    {
        $this->dateOfBill = $dateOfBill;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getFormattedTitle(){
        return $this->getBillNumber() .'--'. $this->getBillAmount().'--'.$this->getUploadedBy()->getUsername();
    }
}