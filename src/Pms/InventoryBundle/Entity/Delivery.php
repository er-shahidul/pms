<?php

namespace Pms\InventoryBundle\Entity;

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
 * Delivery
 *
 * @ORM\Table(name="delivery")
 * @ORM\Entity(repositoryClass="Pms\InventoryBundle\Entity\Repository\DeliveryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Delivery
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
     * @Assert\File(maxSize="6000000")
     */
    private $file;
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $fileTwo;

    /**
     * @var string
     *
     * @ORM\Column(name="get_pass", type="string", length=255)
     */
    private $getPass;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="delivery")
     * @ORM\JoinColumn(name="project_id", nullable=true)
     */
    private $project;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project")
     * @ORM\JoinColumn(name="issued_to_project_id", nullable = true)
     */
    private $issuedToProject;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\DeliveryItem", mappedBy="delivery", cascade={"persist", "remove"})
     */
    private $deliveryItem;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_type", type="string", length=255, nullable = true)
     */
    private $deliveryType;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="customer_id", nullable=true)
     */
    private $customerId;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="issued_to_customer", nullable=true)
     */
    private $issuedToCustomer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pathTwo;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User",inversedBy="delivery")
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $createdBy;


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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set getPass
     *
     * @param string $getPass
     * @return Delivery
     */
    public function setGetPass($getPass)
    {
        $this->getPass = $getPass;

        return $this;
    }

    /**
     * Get getPass
     *
     * @return string 
     */
    public function getGetPass()
    {
        return $this->getPass;
    }


    /**
     * Set status
     *
     * @param string $status
     * @return Delivery
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set customerId
     *
     * @param User $customerId
     * @return Delivery
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return User
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return Delivery
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


    public function getDateOfRequiredText() {
        if(empty($this->createdDate)){
            return "";
        }

        return $this->getCreatedDate()->format('Y-m-d');
    }

    public function setDateOfRequiredText($date = "") {

        if(!empty($date)){
            return $this->setCreatedDate(new \DateTime($date));
        }

        return $this;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return Delivery
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
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return ArrayCollection
     */
    public function getDeliveryItem()
    {
        return $this->deliveryItem;
    }

    /**
     * @param ArrayCollection $deliveryItem
     */
    public function setDeliveryItem($deliveryItem)
    {
        $this->deliveryItem = $deliveryItem;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
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


    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }
    public function getAbsolutePathTwo()
    {
        return null === $this->pathTwo
            ? null
            : $this->getUploadRootDir().'/'.$this->pathTwo;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    public function getWebPathTwo()
    {
        return null === $this->pathTwo
            ? null
            : $this->getUploadDir().'/'.$this->pathTwo;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/DeliveryDocuments';
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }
    public function uploadTwo()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFileTwo()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFileTwo()->move(
            $this->getUploadRootDir(),
            $this->getFileTwo()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->pathTwo = $this->getFileTwo()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->fileTwo = null;
    }

    public function getAbsolutePathByIndex($index) {
        switch($index) {
            case 1: return $this->getAbsolutePath();
            case 2: return $this->getAbsolutePathTwo();
        }
    }

    public function getWebPathByIndex($index) {
        switch($index) {
            case 1: return $this->getWebPath();
            case 2: return $this->getWebPathTwo();
        }
    }

    /**
     * @return mixed
     */
    public function getPathTwo()
    {
        return $this->pathTwo;
    }

    /**
     * @param mixed $pathTwo
     */
    public function setPathTwo($pathTwo)
    {
        $this->pathTwo = $pathTwo;
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
     * Sets fileTwo.
     *
     * @param UploadedFile $fileTwo
     */
    public function setFileTwo(UploadedFile $fileTwo = null)
    {
        $this->fileTwo = $fileTwo;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return CostHeader
     */
    public function getCostHeader()
    {
        return $this->costHeader;
    }

    /**
     * @param CostHeader $costHeader
     */
    public function setCostHeader($costHeader)
    {
        $this->costHeader = $costHeader;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * @param SubCategory $subCategory
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;
    }

    /**
     * @return User
     */
    public function getIssuedToCustomer()
    {
        return $this->issuedToCustomer;
    }

    /**
     * @param User $issuedToCustomer
     */
    public function setIssuedToCustomer($issuedToCustomer)
    {
        $this->issuedToCustomer = $issuedToCustomer;
    }

    /**
     * @return Project
     */
    public function getIssuedToProject()
    {
        return $this->issuedToProject;
    }

    /**
     * @param Project $issuedToProject
     */
    public function setIssuedToProject($issuedToProject)
    {
        $this->issuedToProject = $issuedToProject;
    }

    /**
     * @return string
     */
    public function getDeliveryType()
    {
        return $this->deliveryType;
    }

    /**
     * @param string $deliveryType
     */
    public function setDeliveryType($deliveryType)
    {
        $this->deliveryType = $deliveryType;
    }


}
