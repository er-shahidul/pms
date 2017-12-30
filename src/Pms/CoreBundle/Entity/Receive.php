<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\DocumentBundle\Entity\Document;
use Pms\SettingBundle\Entity\Vendor;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Receive
 *
 * @ORM\Table(name="receives")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\ReceiveRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Receive
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
     * @ORM\JoinColumn(name="received_by", nullable=true)
     */
    private $receivedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="received_date", type="datetime")
     */
    private $receivedDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bill_date", type="datetime", nullable=true)
     */
    private $billDate;

    /**
     * @var string
     *
     * @ORM\Column(name="bill_number", type="string", length=255, nullable=true)
     */
    private $billNumber;

    /**
     * @var Document
     *
     * @ORM\ManyToOne(targetEntity="Pms\DocumentBundle\Entity\Document", inversedBy="receiveInvoice", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="invoices")
     */
    private $invoice;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReadyForPayment", mappedBy="grn", cascade={"persist", "remove"})
     */
    private $readyForPayments;

    /**
     * @var Document
     *
     * @ORM\ManyToOne(targetEntity="Pms\DocumentBundle\Entity\Document", inversedBy="receiveChallan", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="calans")
     */
    private $calan;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_grn", type="string", length=255, nullable=true)
     */
    private $refGrn;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_number", type="string", length=255, nullable=true)
     */
    private $contactNumber;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReceivedItem", mappedBy="receive", cascade={"persist", "remove"})
     */
    private $receiveItems;

    /**
     * @var Vendor
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Vendor", inversedBy="receive")
     * @ORM\JoinColumn(name="vendors", nullable=true)
     */
    private $vendor;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="buyers", nullable=true)
     */
    private $buyer;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_date", type="datetime", nullable=true)
     */
    private $closedDate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Log\ReceiveLog", mappedBy="receive", cascade={"persist", "remove"})
     */
    private $receiveLogs;

    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $file;

    public $temp;

    /**
     * @ORM\Column(name="path_two",type="string", length=255, nullable=true)
     */
    protected $pathTwo;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $fileTwo;

    public $tempTwo;

    /**
     * @ORM\Column(name="path_three",type="string", length=255, nullable=true)
     */
    protected $pathThree;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $fileThree;

    public $tempThree;

    /**
     * @var integer
     *
     * @ORM\Column(name="approve_status", type="integer", nullable=true)
     */
    private $approveStatus;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_one", nullable=true)
     */
    private $approvedOne;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_one_date", type="datetime", nullable=true)
     */
    private $approvedOneDate;

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
     * @var string
     *
     * @ORM\Column(name="reply_send_back_comment", type="string", nullable=true)
     */
    private $replySendBackComments;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="sendBack_by", nullable=true)
     */
    private $sendBackBy;


    /**
     * @return string
     */
    public function getCompanyType()
    {
        $companyName = '';

        $receiveItems = $this->getReceiveItems();
        foreach ($receiveItems as $receiveItem) {
            $companyName = $receiveItem->getPurchaseOrderItem()->getPurchaseRequisitionItem()
                                       ->getPurchaseRequisition()->getProject()
                                       ->getProjectCategory()->getProjectCategoryName();
        }

        return $companyName;
    }

    /**
     * @return string
     */
    public function getPo()
    {
        $po = '';

        $receiveItems = $this->getReceiveItems();
        foreach ($receiveItems as $receiveItem) {
            $po = $receiveItem->getPurchaseOrderItem()->getPurchaseOrder();
        }

        return $po;
    }

    /**
     * Set approveStatus
     *
     * @param integer $approveStatus
     * @return Receive
     */
    public function setApproveStatus($approveStatus)
    {
        $this->approveStatus = $approveStatus;

        return $this;
    }

    /**
     * Get approveStatus
     *
     * @return integer
     */
    public function getApproveStatus()
    {
        return $this->approveStatus;
    }

    /**
     * Set approvedOne
     *
     * @param User $approvedOne
     * @return Receive
     */
    public function setApprovedOne($approvedOne)
    {
        $this->approvedOne = $approvedOne;

        return $this;
    }

    /**
     * Get approvedOne
     *
     * @return User
     */
    public function getApprovedOne()
    {
        return $this->approvedOne;
    }

    /**
     * Set approvedOneDate
     *
     * @param \DateTime $approvedOneDate
     * @return Receive
     */
    public function setApprovedOneDate($approvedOneDate)
    {
        $this->approvedOneDate = $approvedOneDate;

        return $this;
    }

    /**
     * Get approvedOneDate
     *
     * @return \DateTime
     */
    public function getApprovedOneDate()
    {
        return $this->approvedOneDate;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReceiveLogs()
    {
        return $this->receiveLogs;
    }


    public function getAbsolutePathByIndex($index) {
        switch($index) {
            case 1: return $this->getAbsolutePath();
            case 2: return $this->getAbsolutePathTwo();
            case 3: return $this->getAbsolutePathThree();
        }
    }

    public function getWebPathByIndex($index) {
        switch($index) {
            case 1: return $this->getWebPath();
            case 2: return $this->getWebPathTwo();
            case 3: return $this->getWebPathThree();
        }
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadTwo()
    {
        if (null !== $this->getFileTwo()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->pathTwo = $filename . '.' . $this->getFileTwo()->guessExtension();
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadThree()
    {
        if (null !== $this->getFileThree()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->pathThree = $filename . '.' . $this->getFileThree()->guessExtension();
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
     * Get fileTwo.
     *
     * @return UploadedFile
     */
    public function getFileTwo()
    {
        return $this->fileTwo;
    }

    /**
     * Get fileThree.
     *
     * @return UploadedFile
     */
    public function getFileThree()
    {
        return $this->fileThree;
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
     * Sets fileTwo.
     *
     * @param UploadedFile $fileTwo
     */
    public function setFileTwo(UploadedFile $fileTwo = null)
    {
        $this->fileTwo = $fileTwo;
        // check if we have an old image path
        if (isset($this->pathTwo)) {
            // store the old name to delete after the update
            $this->tempTwo = $this->pathTwo;
            $this->pathTwo = null;
        } else {
            $this->pathTwo = 'initial';
        }
    }

    /**
     * Sets fileThree.
     *
     * @param UploadedFile $fileThree
     */
    public function setFileThree(UploadedFile $fileThree = null)
    {
        $this->fileThree = $fileThree;
        // check if we have an old image path
        if (isset($this->pathThree)) {
            // store the old name to delete after the update
            $this->tempThree = $this->pathThree;
            $this->pathThree = null;
        } else {
            $this->pathThree = 'initial';
        }
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
     * Get pathTwo
     *
     * @return string
     */
    public function getPathTwo()
    {
        return $this->pathTwo;
    }

    /**
     * Get pathThree
     *
     * @return string
     */
    public function getPathThree()
    {
        return $this->pathThree;
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

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadTwo()
    {
        if (null === $this->getFileTwo()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFileTwo()->move($this->getUploadRootDir(), $this->pathTwo);

        // check if we have an old image
        if (isset($this->tempTwo)) {
            // delete the old image
            unlink($this->getUploadRootDir() . '/' . $this->tempTwo);
            // clear the temp image path
            $this->tempTwo = null;
        }
        $this->fileTwo = null;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadThree()
    {
        if (null === $this->getFileThree()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFileThree()->move($this->getUploadRootDir(), $this->pathThree);

        // check if we have an old image
        if (isset($this->tempThree)) {
            // delete the old image
            unlink($this->getUploadRootDir() . '/' . $this->tempThree);
            // clear the temp image path
            $this->tempThree = null;
        }
        $this->fileThree = null;
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/receive';
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

    /**
     * @ORM\PostRemove()
     */
    public function removeUploadTwo()
    {
        if ($fileTwo = $this->getAbsolutePathTwo()) {
            unlink($fileTwo);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUploadThree()
    {
        if ($fileThree = $this->getAbsolutePathThree()) {
            unlink($fileThree);
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

    public function getAbsolutePathTwo()
    {
        return null === $this->pathTwo
            ? null
            : $this->getUploadRootDir() . '/' . $this->pathTwo;
    }

    public function getWebPathTwo()
    {
        return null === $this->pathTwo
            ? null
            : $this->getUploadDir() . '/' . $this->pathTwo;
    }

    public function getAbsolutePathThree()
    {
        return null === $this->pathThree
            ? null
            : $this->getUploadRootDir() . '/' . $this->pathThree;
    }

    public function getWebPathThree()
    {
        return null === $this->pathThree
            ? null
            : $this->getUploadDir() . '/' . $this->pathThree;
    }

    public function __construct()
    {
        $this->receiveItems = new ArrayCollection();
    }

    /**
     * Set closedDate
     *
     * @param \DateTime $closedDate
     * @return Receive
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
     * Set status
     *
     * @param integer $status
     * @return Receive
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
     * Set buyer
     *
     * @param User $buyer
     * @return Receive
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * Get buyer
     *
     * @return User
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * Set vendor
     *
     * @param integer $vendor
     * @return Receive
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get vendor
     *
     * @return integer
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    public function addReceiveItem(ReceivedItem $item)
    {
        $item->setReceive($this);

        if (!$this->getReceiveItems()->contains($item)) {
            $this->receiveItems->add($item);
        }

        return $this;
    }

    public function removeReceiveItem(ReceivedItem $item)
    {
        if ($this->getReceiveItems()->contains($item)) {
            $this->getReceiveItems()->removeElement($item);
        }
    }

    public function getReceiveItems()
    {
        return $this->receiveItems;
    }

    /**
     * Set invoice
     *
     * @param Document $invoice
     * @return Receive
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return Document
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set calan
     *
     * @param Document $calan
     * @return Receive
     */
    public function setCalan($calan)
    {
        $this->calan = $calan;

        return $this;
    }

    /**
     * Get calan
     *
     * @return Document
     */
    public function getCalan()
    {
        return $this->calan;
    }

    /**
     * Get refGrn
     *
     * @return string
     */
    public function getRefGrn()
    {
        return $this->refGrn;
    }

    /**
     * Set refGrn
     *
     * @param string $refGrn
     * @return Receive
     */
    public function setRefGrn($refGrn)
    {
        $this->refGrn = $refGrn;

        return $this;
    }

    /**
     * Set contactNumber
     *
     * @param string $contactNumber
     * @return Receive
     */
    public function setContactNumber($contactNumber)
    {
        $this->contactNumber = $contactNumber;

        return $this;
    }

    /**
     * Get contactNumber
     *
     * @return string
     */
    public function getContactNumber()
    {
        return $this->contactNumber;
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
     * Set receivedBy
     *
     * @param User $receivedBy
     * @return Receive
     */
    public function setReceivedBy($receivedBy)
    {
        $this->receivedBy = $receivedBy;

        return $this;
    }

    /**
     * Get receivedBy
     *
     * @return User
     */
    public function getReceivedBy()
    {
        return $this->receivedBy;
    }

    /**
     * Set receivedDate
     *
     * @param \DateTime $receivedDate
     * @return Receive
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * Get receivedDate
     *
     * @return \DateTime
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    public function getPaymentType() {

        if($this->getReceiveItems()->count() > 0) {
            /** @var ReceivedItem $receiveItem */
            $receiveItem = $this->getReceiveItems()->get(0);
            return $receiveItem->getPurchaseOrderItem()->getPurchaseOrder()->getPaymentType();
        }
    }

    public function getPaymentMethod() {

        if($this->getReceiveItems()->count() > 0) {
            /** @var ReceivedItem $receiveItem */
            $receiveItem = $this->getReceiveItems()->get(0);
            return $receiveItem->getPurchaseOrderItem()->getPurchaseOrder()->getPaymentMethod();
        }
    }

    public function getPoIssued() {

        if($this->getReceiveItems()->count() > 0) {
            /** @var ReceivedItem $receiveItem */
            $receiveItem = $this->getReceiveItems()->get(0);
            return $receiveItem->getPurchaseOrderItem()->getPurchaseOrder()->getCreatedBy();
        }
    }

    public function getPoOrder() {

        if($this->getReceiveItems()->count() > 0) {
            /** @var ReceivedItem $receiveItem */
            $receiveItem = $this->getReceiveItems()->get(0);
            return $receiveItem->getPurchaseOrderItem()->getPurchaseOrder()->getId();
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getReadyForPayments()
    {
        return $this->readyForPayments;
    }

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
     * @return int
     */
    public function getSendBackStatus()
    {
        return $this->sendBackStatus;
    }

    /**
     * @param int $sendBackStatus
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

    /**
     * @return string
     */
    public function getReplySendBackComments()
    {
        return $this->replySendBackComments;
    }

    /**
     * @param string $replySendBackComments
     */
    public function setReplySendBackComments($replySendBackComments)
    {
        $this->replySendBackComments = $replySendBackComments;
    }

    /**
     * @return User
     */
    public function getSendBackBy()
    {
        return $this->sendBackBy;
    }

    /**
     * @param User $sendBackBy
     */
    public function setSendBackBy($sendBackBy)
    {
        $this->sendBackBy = $sendBackBy;
    }
}
