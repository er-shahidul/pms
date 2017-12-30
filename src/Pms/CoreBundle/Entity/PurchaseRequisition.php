<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Category;
use Pms\SettingBundle\Entity\CostHeader;
use Pms\SettingBundle\Entity\Project;
use Pms\SettingBundle\Entity\SubCategory;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PurchaseRequisition
 *
 * @ORM\Table(name="purchase_requisitions")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseRequisitionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PurchaseRequisition
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
     * @ORM\Column(name="ref_no", type="string" , length=255, nullable=true)
     */
    private $refNo;

    /**
     * @var float
     *
     * @ORM\Column(name="cost", type="float", nullable=true)
     */
    private $prCost;

    /**
     * @var CostHeader
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\CostHeader", inversedBy="purchaseRequisition")
     * @ORM\JoinColumn(name="cost_headers", nullable=true)
     */
    private $costHeader;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Category", inversedBy="purchaseRequisition")
     * @ORM\JoinColumn(name="categories", nullable=true)
     */
    private $category;

    /**
     * @var SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\SubCategory", inversedBy="purchaseRequisition")
     * @ORM\JoinColumn(name="sub_categories", nullable=true)
     */
    private $subCategory;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var /DateTime
     *
     * @ORM\Column(name="date_of_requisitions", type="datetime", nullable=true)
     */
    private $dateOfRequisition;

    /**
     * @var /DateTime
     *
     * @ORM\Column(name="closed_date", type="date", nullable=true)
     */
    private $closedDate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisitionItem", mappedBy="purchaseRequisition", cascade={"persist", "remove"})
     */
    private $purchaseRequisitionItems;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\Log\PrLog", mappedBy="purchaseRequisition", cascade={"persist", "remove"})
     */
    private $prLogs;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="purchaseRequisition")
     * @ORM\JoinColumn(name="projects", nullable=true)
     */
    private $project;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $createdBy;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisitionComment", mappedBy="purchaseRequisition", cascade={"persist", "remove"})
     */
    private $purchaseRequisitionComment;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_by_project_head", nullable=true)
     */
    private $approvedByProjectHead;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_date_project_head", type="datetime", nullable=true)
     */
    private $approvedDateProjectHead;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_by_category_head_one", nullable=true)
     */
    private $approvedByCategoryHeadOne;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_date_category_head_one", type="datetime", nullable=true)
     */
    private $approvedDateCategoryHeadOne;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="approved_by_category_head_two", nullable=true)
     */
    private $approvedByCategoryHeadTwo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="approved_date_category_head_two", type="datetime", nullable=true)
     */
    private $approvedDateCategoryHeadTwo;

    /**
     * @var integer
     *
     * @ORM\Column(name="approve_status", type="integer", nullable=true)
     */
    private $approveStatus;

    /**
     * @var float
     *
     * @ORM\Column(name="total_requisition_item_quantity", type="float", nullable=true)
     */
    private $totalRequisitionItemQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="total_order_item_quantity", type="float", nullable=true)
     */
    private $totalOrderItemQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="total_requisition_item", type="float", nullable=true)
     */
    private $totalRequisitionItem;

    /**
     * @var float
     *
     * @ORM\Column(name="total_requisition_item_claimed", type="float", nullable=true)
     */
    private $totalRequisitionItemClaimed;

    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", nullable=true)
     */
    private $priority;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseRequisitionComment()
    {
        return $this->purchaseRequisitionComment;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrLogs()
    {
        return $this->prLogs;
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
        return 'uploads/purchaseRequisition';
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($fileThree = $this->getAbsolutePathThree()) {
            unlink($fileThree);
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

    /**
     * Set costHeader
     *
     * @param CostHeader $costHeader
     * @return PurchaseRequisition
     */
    public function setCostHeader($costHeader)
    {
        $this->costHeader = $costHeader;

        return $this;
    }

    /**
     * Get costHeader
     *
     * @return CostHeader
     */
    public function getCostHeader()
    {
        return $this->costHeader;
    }

    /**
     * Set category
     *
     * @param Category $category
     * @return PurchaseRequisition
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set subCategory
     *
     * @param SubCategory $subCategory
     * @return PurchaseRequisition
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get subCategory
     *
     * @return SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    public function __construct()
    {
        $this->purchaseRequisitionItems = new ArrayCollection();
    }

    /**
     * @param \Pms\CoreBundle\Entity\PurchaseRequisitionItem $purchaseRequisitionItem
     */
    public function addPurchaseRequisitionItem($purchaseRequisitionItem)
    {
        if (!$this->getPurchaseRequisitionItems()->contains($purchaseRequisitionItem)) {
            $purchaseRequisitionItem->setPurchaseRequisition($this);
            $this->getPurchaseRequisitionItems()->add($purchaseRequisitionItem);
        }
    }

    /**
     * @param \Pms\CoreBundle\Entity\PurchaseRequisitionItem $purchaseRequisitionItem
     */
    public function removePurchaseRequisitionItem($purchaseRequisitionItem)
    {
        if ($this->getPurchaseRequisitionItems()->contains($purchaseRequisitionItem)) {
            $this->getPurchaseRequisitionItems()->removeElement($purchaseRequisitionItem);
        }
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
     * Set refNo
     *
     * @param integer $refNo
     * @return PurchaseRequisition
     */
    public function setRefNo($refNo)
    {
        $this->refNo = $refNo;

        return $this;
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
     * Set prCost
     *
     * @param float $prCost
     * @return PurchaseRequisition
     */
    public function setPrCost($prCost)
    {
        $this->prCost = $prCost;

        return $this;
    }

    /**
     * Get prCost
     *
     * @return float
     */
    public function getPrCost()
    {
        return $this->prCost;
    }

    /**
     * Set dateOfRequisition
     *
     * @param /DateTime $dateOfRequisition
     * @return PurchaseRequisition
     */
    public function setDateOfRequisition($dateOfRequisition)
    {
        $this->dateOfRequisition = $dateOfRequisition;

        return $this;
    }

    /**
     * Get dateOfRequisition
     *
     * @return /DateTime
     */
    public function getDateOfRequisition()
    {
        return $this->dateOfRequisition;
    }

    /**
     * Set status
     * 1 Approve
     * 2 Open
     * @param integer $status
     * @return PurchaseRequisition
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
     * Set totalRequisitionItemQuantity
     *
     * @param float $totalRequisitionItemQuantity
     * @return PurchaseRequisition
     */
    public function setTotalRequisitionItemQuantity($totalRequisitionItemQuantity)
    {
        $this->totalRequisitionItemQuantity = $totalRequisitionItemQuantity;

        return $this;
    }

    /**
     * Get totalRequisitionItemQuantity
     *
     * @return float
     */
    public function getTotalRequisitionItemQuantity()
    {
        return $this->totalRequisitionItemQuantity;
    }

    /**
     * Set totalOrderItemQuantity
     *
     * @param float $totalOrderItemQuantity
     * @return PurchaseRequisition
     */
    public function setTotalOrderItemQuantity($totalOrderItemQuantity)
    {
        $this->totalOrderItemQuantity = $totalOrderItemQuantity;

        return $this;
    }

    /**
     * Get totalOrderItemQuantity
     *
     * @return float
     */
    public function getTotalOrderItemQuantity()
    {
        return $this->totalOrderItemQuantity;
    }

    /**
     * Set totalRequisitionItem
     *
     * @param float $totalRequisitionItem
     * @return PurchaseRequisition
     */
    public function setTotalRequisitionItem($totalRequisitionItem)
    {
        $this->totalRequisitionItem = $totalRequisitionItem;

        return $this;
    }

    /**
     * Get totalRequisitionItem
     *
     * @return float
     */
    public function getTotalRequisitionItem()
    {
        return $this->totalRequisitionItem;
    }

    /**
     * Set totalRequisitionItemClaimed
     *
     * @param float $totalRequisitionItemClaimed
     * @return PurchaseRequisition
     */
    public function setTotalRequisitionItemClaimed($totalRequisitionItemClaimed)
    {
        $this->totalRequisitionItemClaimed = $totalRequisitionItemClaimed;

        return $this;
    }

    /**
     * Get totalRequisitionItemClaimed
     *
     * @return float
     */
    public function getTotalRequisitionItemClaimed()
    {
        return $this->totalRequisitionItemClaimed;
    }

    /**
     * Set project
     *
     * @param Project $project
     * @return PurchaseRequisition
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return PurchaseRequisition
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     * @return PurchaseRequisition
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
     * @return PurchaseRequisition
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
     * Set closedBy
     *
     * @param User $closedBy
     * @return PurchaseRequisition
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
     * Set createdBy
     *
     * @param User $createdBy
     * @return PurchaseRequisition
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
     * @return PurchaseRequisition
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
     * Set approvedByProjectHead
     *
     * @param User $approvedByProjectHead
     * @return PurchaseRequisition
     */
    public function setApprovedByProjectHead($approvedByProjectHead)
    {
        $this->approvedByProjectHead = $approvedByProjectHead;

        return $this;
    }

    /**
     * Get approvedByProjectHead
     *
     * @return User
     */
    public function getApprovedByProjectHead()
    {
        return $this->approvedByProjectHead;
    }

    /**
     * Set closedDate
     *
     * @param /DateTime $closedDate
     * @return PurchaseRequisition
     */
    public function setClosedDate($closedDate)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Get closedDate
     *
     * @return /DateTime
     */
    public function getClosedDate()
    {
        return $this->closedDate;
    }

    /**
     * Set approveStatus
     *
     * @param integer $approveStatus
     * @return PurchaseRequisition
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
     * Set approvedDateProjectHead
     *
     * @param \DateTime $approvedDateProjectHead
     * @return PurchaseRequisition
     */
    public function setApprovedDateProjectHead($approvedDateProjectHead)
    {
        $this->approvedDateProjectHead = $approvedDateProjectHead;

        return $this;
    }

    /**
     * Get approvedDateProjectHead
     *
     * @return \DateTime
     */
    public function getApprovedDateProjectHead()
    {
        return $this->approvedDateProjectHead;
    }

    /**
     * Set approvedByCategoryHeadOne
     *
     * @param User $approvedByCategoryHeadOne
     * @return PurchaseRequisition
     */
    public function setApprovedByCategoryHeadOne($approvedByCategoryHeadOne)
    {
        $this->approvedByCategoryHeadOne = $approvedByCategoryHeadOne;

        return $this;
    }

    /**
     * Get approvedByCategoryHeadOne
     *
     * @return User
     */
    public function getApprovedByCategoryHeadOne()
    {
        return $this->approvedByCategoryHeadOne;
    }

    /**
     * Set approvedDateCategoryHeadOne
     *
     * @param \DateTime $approvedDateCategoryHeadOne
     * @return PurchaseRequisition
     */
    public function setApprovedDateCategoryHeadOne($approvedDateCategoryHeadOne)
    {
        $this->approvedDateCategoryHeadOne = $approvedDateCategoryHeadOne;

        return $this;
    }

    /**
     * Get approvedDateCategoryHeadOne
     *
     * @return \DateTime
     */
    public function getApprovedDateCategoryHeadOne()
    {
        return $this->approvedDateCategoryHeadOne;
    }

    /**
     * Set approvedByCategoryHeadTwo
     *
     * @param User $approvedByCategoryHeadTwo
     * @return PurchaseRequisition
     */
    public function setApprovedByCategoryHeadTwo($approvedByCategoryHeadTwo)
    {
        $this->approvedByCategoryHeadTwo = $approvedByCategoryHeadTwo;

        return $this;
    }

    /**
     * Get approvedByCategoryHeadTwo
     *
     * @return User
     */
    public function getApprovedByCategoryHeadTwo()
    {
        return $this->approvedByCategoryHeadTwo;
    }

    /**
     * Set approvedDateCategoryHeadTwo
     *
     * @param \DateTime $approvedDateCategoryHeadTwo
     * @return PurchaseRequisition
     */
    public function setApprovedDateCategoryHeadTwo($approvedDateCategoryHeadTwo)
    {
        $this->approvedDateCategoryHeadTwo = $approvedDateCategoryHeadTwo;

        return $this;
    }

    /**
     * Get approvedDateCategoryHeadTwo
     *
     * @return \DateTime
     */
    public function getApprovedDateCategoryHeadTwo()
    {
        return $this->approvedDateCategoryHeadTwo;
    }

    public function getPurchaseRequisitionItems()
    {
        return $this->purchaseRequisitionItems;
    }

    public function getDateOfRequisitionText()
    {
        if(empty($this->dateOfRequisition)){
            return "";
        }

        return $this->getDateOfRequisition()->format('Y-m-d');
    }

    public function setDateOfRequisitionText($date = "")
    {
        if(!empty($date)){
            return $this->setDateOfRequisition(new \DateTime($date));
        }

        return $this;
    }
}