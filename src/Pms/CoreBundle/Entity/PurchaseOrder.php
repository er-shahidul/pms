<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\PurchaseType;
use Pms\SettingBundle\Entity\Vendor;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PurchaseOrder
 *
 * @ORM\Table(name="purchase_orders")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseOrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PurchaseOrder
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderItem", mappedBy="purchaseOrder", cascade={"persist", "remove"})
     */
    private $purchaseOrderItems;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_no", type="string", nullable=true)
     */
    private $refNo;

    /**
     * @var PurchaseType
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\PurchaseType", inversedBy="purchaseOrder")
     * @ORM\JoinColumn(name="po_nonpo", nullable=true)
     */
     private $poNonpo;


    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="amendment_status", type="integer", nullable=true)
     */
    private $amendmentStatus;
    /**
     * @var integer
     *
     * @ORM\Column(name="amendment_by_po_no", type="integer", nullable=true)
     */
    private $amendmentByPoNo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="advance_status", type="boolean", nullable=true)
     */
    private $advanceStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="computation_status", type="integer", nullable=true)
     */
    private $computationStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", nullable=true)
     */
    private $paymentType;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_from", type="integer", nullable=true)
     */
    private $paymentFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="vendor_quotation_reference_number", type="string", nullable=true)
     */
    private $vendorQuotationReferenceNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="approve_status", type="integer", nullable=true)
     */
    private $approveStatus;

    /**
     * @var Vendor
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Vendor", inversedBy="purchaseOrder")
     * @ORM\JoinColumn(name="vendors", nullable=true)
     */
    private $vendor;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\DocumentBundle\Entity\Document", mappedBy="purchaseOrder")
     */
    private $document;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="buyers", nullable=true)
     */
    private $buyer;

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
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by", nullable=true)
     */
    private $updatedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_date", type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var /DateTime
     *
     * @ORM\Column(name="date_of_closings", type="date", nullable=true)
     */
    private $dateOfClosing;

    /**
     * @var /DateTime
     *
     * @ORM\Column(name="date_of_delivered", type="date", nullable=true)
     */
    private $dateOfDelivered;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_two", nullable=true)
     */
    private $approvedTwo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_two_date", type="datetime", nullable=true)
     */
    private $approvedTwoDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_three", nullable=true)
     */
    private $approvedThree;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_three_date", type="datetime", nullable=true)
     */
    private $approvedThreeDate;

    /**
     * @var float
     *
     * @ORM\Column(name="total_order_item_quantity", type="float", nullable=true)
     */
    private $totalOrderItemQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="total_order_receive_quantity", type="float", nullable=true)
     */
    private $totalOrderReceiveQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="sub_total", type="float", nullable=true)
     */
    private $subTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="tax", type="float", nullable=true)
     */
    private $tax;

    /**
     * @var float
     *
     * @ORM\Column(name="net_total", type="float", nullable=true)
     */
    private $netTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="advance_payment_amount", type="float", nullable=true)
     */
    private $advancePaymentAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="total_order_item", type="float", nullable=true)
     */
    private $totalOrderItem;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", nullable=true)
     */
    private $paymentMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", nullable=true)
     */
    private $comment;
    /**
     * @var string
     *
     * @ORM\Column(name="custom_term_condition", type="text", nullable=true)
     */
    private $customTermCondition;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

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
     * @ORM\Column(name="path_amendment",type="string", length=255, nullable=true)
     */
    protected $pathAmendment;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $fileAmendment;

    public $tempAmendment;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderComment", mappedBy="purchaseOrder", cascade={"persist", "remove"})
     */
    private $purchaseOrderComment;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReadyForPayment", mappedBy="purchaseOrder", cascade={"persist", "remove"})
     */
    private $readyForPayments;

     /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Payment", mappedBy="purchaseOrder", cascade={"persist", "remove"})
     */
    private $payment;

    /**
     * @var string
     *
     * @ORM\Column(name="amendment_comment", type="string", nullable=true)
     */
    private $amendmentComment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_advance_payment", type="boolean", nullable=true)
     */
    private $isAdvancePayment = 0;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Log\PoLog", mappedBy="purchaseOrder", cascade={"persist", "remove"})
     */
    private $poLogs;

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPoLogs()
    {
        return $this->poLogs;
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     * @return PurchaseOrder
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     * @return PurchaseOrder
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseOrderComment()
    {
        return $this->purchaseOrderComment;
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

    public function getAbsolutePathByIndex($index) {
        switch($index) {
            case 1: return $this->getAbsolutePath();
            case 2: return $this->getAbsolutePathTwo();
            case 3: return $this->getAbsolutePathThree();
            case 4: return $this->getAbsolutePathAmendment();
        }
    }

    public function getWebPathByIndex($index) {
        switch($index) {
            case 1: return $this->getWebPath();
            case 2: return $this->getWebPathTwo();
            case 3: return $this->getWebPathThree();
            case 4: return $this->getWebPathAmendment();
        }
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
     * Get pathAmendment
     *
     * @return string
     */
    public function getPathAmendment()
    {
        return $this->pathAmendment;
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadAmendment()
    {
        if (null !== $this->getFileAmendment()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->pathAmendment = $filename . '.' . $this->getFileAmendment()->guessExtension();
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
     * @return string
     */
    public function getCompanyType()
    {
        $companyName = '';
        $purchaseOrderItems = $this->getPurchaseOrderItems();
        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $companyName = $purchaseOrderItem->getPurchaseRequisitionItem()->getPurchaseRequisition()->getProject()->getProjectCategory()->getProjectCategoryName();
        }

        return $companyName;
    }

    /**
     * @return array
     */
    public function getProjectName()
    {
        $projectName = array();
        $purchaseOrderItems = $this->getPurchaseOrderItems();
        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $projectName[] = $purchaseOrderItem->getPurchaseRequisitionItem()->getPurchaseRequisition()->getProject()->getProjectName();
        }

        return $projectName;
    }

    /**
     * @return array
     */
    public function getPrNo()
    {
        $prNo = array();
        $purchaseOrderItems = $this->getPurchaseOrderItems();
        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $prNo[] = $purchaseOrderItem->getPurchaseRequisitionItem()->getPurchaseRequisition()->getId();
        }

        return $prNo;
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
     * Get fileAmendment.
     *
     * @return UploadedFile
     */
    public function getFileAmendment()
    {
        return $this->fileAmendment;
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
     * Sets fileAmendment.
     *
     * @param UploadedFile $fileAmendment
     */
    public function setFileAmendment(UploadedFile $fileAmendment = null)
    {
        $this->fileAmendment = $fileAmendment;
        // check if we have an old image path
        if (isset($this->pathAmendment)) {
            // store the old name to delete after the update
            $this->tempAmendment = $this->pathAmendment;
            $this->pathAmendment = null;
        } else {
            $this->pathAmendment = 'initial';
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

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadAmendment()
    {
        if (null === $this->getFileAmendment()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFileAmendment()->move($this->getUploadRootDir(), $this->pathAmendment);

        // check if we have an old image
        if (isset($this->tempAmendment)) {
            // delete the old image
            unlink($this->getUploadRootDir() . '/' . $this->tempAmendment);
            // clear the temp image path
            $this->tempAmendment = null;
        }
        $this->fileAmendment = null;
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
        return 'uploads/purchaseOrder';
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

    /**
     * @ORM\PostRemove()
     */
    public function removeUploadAmendment()
    {
        if ($fileAmendment = $this->getAbsolutePathAmendment()) {
            unlink($fileAmendment);
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

    public function getAbsolutePathAmendment()
    {
        return null === $this->pathAmendment
            ? null
            : $this->getUploadRootDir() . '/' . $this->pathAmendment;
    }

    public function getWebPathAmendment()
    {
        return null === $this->pathAmendment
            ? null
            : $this->getUploadDir() . '/' . $this->pathAmendment;
    }

    public function __construct()
    {
        $this->purchaseOrderItems = new ArrayCollection();
    }

    /**
     * Set totalOrderItemQuantity
     *
     * @param integer $totalOrderItemQuantity
     * @return PurchaseOrder
     */
    public function setTotalOrderItemQuantity($totalOrderItemQuantity)
    {
        $this->totalOrderItemQuantity = $totalOrderItemQuantity;

        return $this;
    }

    /**
     * Get totalOrderItemQuantity
     *
     * @return integer
     */
    public function getTotalOrderItemQuantity()
    {
        return $this->totalOrderItemQuantity;
    }

    /**
     * Set totalOrderReceiveQuantity
     *
     * @param integer $totalOrderReceiveQuantity
     * @return PurchaseOrder
     */
    public function setTotalOrderReceiveQuantity($totalOrderReceiveQuantity)
    {
        $this->totalOrderReceiveQuantity = $totalOrderReceiveQuantity;

        return $this;
    }

    /**
     * Get totalOrderReceiveQuantity
     *
     * @return integer
     */
    public function getTotalOrderReceiveQuantity()
    {
        return $this->totalOrderReceiveQuantity;
    }

    /**
     * Set totalOrderItem
     *
     * @param integer $totalOrderItem
     * @return PurchaseOrder
     */
    public function setTotalOrderItem($totalOrderItem)
    {
        $this->totalOrderItem = $totalOrderItem;

        return $this;
    }

    /**
     * Get totalOrderItem
     *
     * @return integer
     */
    public function getTotalOrderItem()
    {
        return $this->totalOrderItem;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return PurchaseOrder
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
     * Set amendmentComment
     *
     * @param string $amendmentComment
     * @return PurchaseOrder
     */
    public function setAmendmentComment($amendmentComment)
    {
        $this->amendmentComment = $amendmentComment;

        return $this;
    }

    /**
     * Get amendmentComment
     *
     * @return string
     */
    public function getAmendmentComment()
    {
        return $this->amendmentComment;
    }

    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     * @return PurchaseOrder
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function addPurchaseOrderItem(PurchaseOrderItem $item)
    {
        $item->setPurchaseOrder($this);

        if (!$this->getPurchaseOrderItems()->contains($item)) {
            $this->purchaseOrderItems->add($item);
        }

        return $this;
    }

    public function removePurchaseOrderItem(PurchaseOrderItem $item)
    {
        if ($this->getPurchaseOrderItems()->contains($item)) {
            $this->getPurchaseOrderItems()->removeElement($item);
        }
    }

    public function getPurchaseOrderItems()
    {
        return $this->purchaseOrderItems;
    }
    public function getPurchaseOrderTotalAmount()
    {
        $totalAmount = 0;
        foreach ($this->purchaseOrderItems as $purchaseOrderItem){
            $totalAmount += $purchaseOrderItem->getQuantity() * $purchaseOrderItem->getPrice();
        }
        return $totalAmount;
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
     * Get refNo
     *
     * @return integer
     */
    public function getRefNo()
    {
        return $this->refNo;
    }

    /**
     * Set refNo
     *
     * @param integer $refNo
     * @return PurchaseOrder
     */
    public function setRefNo($refNo)
    {
        $this->refNo = $refNo;

        return $this;
    }

    /**
     * Set buyer
     *
     * @param User $buyer
     * @return PurchaseOrder
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
     * Set createdBy
     *
     * @param User $createdBy
     * @return PurchaseOrder
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
     * @return PurchaseOrder
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
     * Set dateOfDelivered
     *
     * @param /DateTime $dateOfDelivered
     * @return PurchaseOrder
     */
    public function setDateOfDelivered($dateOfDelivered)
    {
        $this->dateOfDelivered = $dateOfDelivered;

        return $this;
    }

    /**
     * Get dateOfDelivered
     *
     * @return /DateTime
     */
    public function getDateOfDelivered()
    {
        return $this->dateOfDelivered;
    }

    /**
     * Set dateOfClosing
     *
     * @param /DateTime $dateOfClosing
     * @return PurchaseOrder
     */
    public function setDateOfClosing($dateOfClosing)
    {
        $this->dateOfClosing = $dateOfClosing;

        return $this;
    }

    /**
     * Get dateOfClosing
     *
     * @return /DateTime
     */
    public function getDateOfClosing()
    {
        return $this->dateOfClosing;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return PurchaseOrder
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
     * Set approveStatus
     *
     * @param integer $approveStatus
     * @return PurchaseOrder
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
     * Set poNonpo
     *
     * @param PurchaseType $poNonpo
     * @return PurchaseOrder
     */
    public function setPoNonpo($poNonpo)
    {
        $this->poNonpo = $poNonpo;

        return $this;
    }

    /**
     * Get poNonpo
     *
     * @return PurchaseType
     */
    public function getPoNonpo()
    {
        return $this->poNonpo;
    }

    /**
     * Set vendor
     *
     * @param Vendor $vendor
     * @return PurchaseOrder
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get vendor
     *
     * @return Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set approvedOne
     *
     * @param User $approvedOne
     * @return PurchaseOrder
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
     * @return PurchaseOrder
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
     * Set approvedTwo
     *
     * @param User $approvedTwo
     * @return PurchaseOrder
     */
    public function setApprovedTwo($approvedTwo)
    {
        $this->approvedTwo = $approvedTwo;

        return $this;
    }

    /**
     * Get approvedTwo
     *
     * @return User
     */
    public function getApprovedTwo()
    {
        return $this->approvedTwo;
    }

    /**
     * Set approvedTwoDate
     *
     * @param \DateTime $approvedTwoDate
     * @return PurchaseOrder
     */
    public function setApprovedTwoDate($approvedTwoDate)
    {
        $this->approvedTwoDate = $approvedTwoDate;

        return $this;
    }

    /**
     * Get approvedTwoDate
     *
     * @return \DateTime
     */
    public function getApprovedTwoDate()
    {
        return $this->approvedTwoDate;
    }

    /**
     * Set approvedThree
     *
     * @param User $approvedThree
     * @return PurchaseOrder
     */
    public function setApprovedThree($approvedThree)
    {
        $this->approvedThree = $approvedThree;

        return $this;
    }

    /**
     * Get approvedThree
     *
     * @return User
     */
    public function getApprovedThree()
    {
        return $this->approvedThree;
    }

    /**
     * Set approvedThreeDate
     *
     * @param \DateTime $approvedThreeDate
     * @return PurchaseOrder
     */
    public function setApprovedThreeDate($approvedThreeDate)
    {
        $this->approvedThreeDate = $approvedThreeDate;

        return $this;
    }

    /**
     * Get approvedThreeDate
     *
     * @return \DateTime
     */
    public function getApprovedThreeDate()
    {
        return $this->approvedThreeDate;
    }

    /**
     * Get subTotal
     *
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * Set subTotal
     *
     * @param float $subTotal
     * @return PurchaseOrder
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
    }

    /**
     * Get tax
     *
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set tax
     *
     * @param float $tax
     * @return PurchaseOrder
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * Get netTotal
     *
     * @return float
     */
    public function getNetTotal()
    {
        return $this->netTotal;
    }

    /**
     * Set netTotal
     *
     * @param float $netTotal
     * @return PurchaseOrder
     */
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;
    }

    /**
     * @return int
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param int $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return int
     */
    public function getPaymentFrom()
    {
        return $this->paymentFrom;
    }

    /**
     * @param int $paymentFrom
     */
    public function setPaymentFrom($paymentFrom)
    {
        $this->paymentFrom = $paymentFrom;
    }

    /**
     * @return string
     */
    public function getVendorQuotationReferenceNumber()
    {
        return $this->vendorQuotationReferenceNumber;
    }

    /**
     * @param string $vendorQuotationReferenceNumber
     */
    public function setVendorQuotationReferenceNumber($vendorQuotationReferenceNumber)
    {
        $this->vendorQuotationReferenceNumber = $vendorQuotationReferenceNumber;
    }

    /**
     * @return int
     */
    public function getComputationStatus()
    {
        return $this->computationStatus;
    }

    /**
     * @param int $computationStatus
     */
    public function setComputationStatus($computationStatus)
    {
        $this->computationStatus = $computationStatus;
    }

    public function getVendorOrBuyer(){
        return $this->vendor ? $this->vendor->getVendorName() : ($this->buyer ? $this->buyer->getUsername(): '');
    }

    /**
     * @return float
     */
    public function getAdvancePaymentAmount()
    {
        return $this->advancePaymentAmount;
    }

    /**
     * @param float $advancePaymentAmount
     */
    public function setAdvancePaymentAmount($advancePaymentAmount)
    {
        $this->advancePaymentAmount = $advancePaymentAmount;
    }

    /**
     * @return ArrayCollection
     */
    public function getReadyForPayments()
    {
        return $this->readyForPayments;
    }

    /**
     * @return boolean
     */
    public function isAdvanceStatus()
    {
        return $this->advanceStatus;
    }

    /**
     * @param boolean $advanceStatus
     */
    public function setAdvanceStatus($advanceStatus)
    {
        $this->advanceStatus = $advanceStatus;
    }

    /**
     * @return string
     */
    public function getCustomTermCondition()
    {
        return $this->customTermCondition;
    }

    /**
     * @param string $customTermCondition
     */
    public function setCustomTermCondition($customTermCondition)
    {
        $this->customTermCondition = $customTermCondition;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return boolean
     */
    public function isIsAdvancePayment()
    {
        return $this->isAdvancePayment;
    }

    /**
     * @param boolean $isAdvancePayment
     */
    public function setIsAdvancePayment($isAdvancePayment)
    {
        $this->isAdvancePayment = $isAdvancePayment;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param ArrayCollection $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return int
     */
    public function getAmendmentStatus()
    {
        return $this->amendmentStatus;
    }

    /**
     * @param int $amendmentStatus
     */
    public function setAmendmentStatus($amendmentStatus)
    {
        $this->amendmentStatus = $amendmentStatus;
    }

    /**
     * @return int
     */
    public function getAmendmentByPoNo()
    {
        return $this->amendmentByPoNo;
    }

    /**
     * @param int $amendmentByPoNo
     */
    public function setAmendmentByPoNo($amendmentByPoNo)
    {
        $this->amendmentByPoNo = $amendmentByPoNo;
    }
}
