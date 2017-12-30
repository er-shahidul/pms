<?php

namespace Pms\BudgetBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Project;
use Pms\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Budget
 *
 * @ORM\Table(name="budgets")
 * @ORM\Entity(repositoryClass="Pms\BudgetBundle\Entity\Repository\BudgetRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Budget
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
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $createdBy;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="budget")
     * @ORM\JoinColumn(name="project", nullable=true)
     */
    private $project;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\BudgetComment", mappedBy="budget", cascade={"persist", "remove"})
     */
    private $budgetComment;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\Log\BudgetLog", mappedBy="budget", cascade={"persist", "remove"})
     */
    private $budgetLogs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
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
     * @var float
     *
     * @ORM\Column(name="net_total", type="float")
     */
    private $netTotal;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\BudgetForSubCategory", mappedBy="budget", cascade={"persist", "remove"})
     */
    private $budgetForSubCategories;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\AdditionalBudgetForSubCategory", mappedBy="budget", cascade={"persist", "remove"})
     */
    private $additionalBudgetForSubCategories;

    /**
     * @var Date
     *
     * @ORM\Column(name="month", type="date", nullable=true)
     */
    private $month;

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
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="approve_status", type="integer")
     */
    private $approveStatus;

    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\File(maxSize="5M")
     */
    public $file;

    public $temp;

    public function __construct()
    {
        $this->budgetForSubCategories = new ArrayCollection();
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
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
        return 'uploads/budget';
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

    public function getAbsolutePathByIndex($index) {
        switch($index) {
            case 1: return $this->getAbsolutePath();
//            case 2: return $this->getAbsolutePathTwo();
//            case 3: return $this->getAbsolutePathThree();
        }
    }

    /**
     * Set updatedBy
     *
     * @param User $updatedBy
     * @return Budget
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
     * @return Budget
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
     * Set project
     *
     * @param Project $project
     * @return Budget
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBudgetComment()
    {
        return $this->budgetComment;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBudgetLogs()
    {
        return $this->budgetLogs;
    }

    /**
     * Set approveStatus
     *
     * @param integer $approveStatus
     * @return Budget
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
     * @return Budget
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
     * @return Budget
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
     * @return Budget
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
     * @return Budget
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
     * @return Budget
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
     * @return Budget
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
     * Set status
     *
     * @param integer $status
     * @return Budget
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
     * Set netTotal
     *
     * @param float $netTotal
     * @return Budget
     */
    public function setNetTotal($netTotal)
    {
        $this->netTotal = $netTotal;

        return $this;
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
     * @param \Pms\BudgetBundle\Entity\BudgetForSubCategory $budget
     */
    public function addBudget($budget)
    {
        if (!$this->getBudgetForSubCategories()->contains($budget)) {
            $budget->setBudget($this);
            $this->getBudgetForSubCategories()->add($budget);
        }
    }

    /**
     * @param \Pms\BudgetBundle\Entity\BudgetForSubCategory $budget
     */
    public function removeBudget($budget)
    {
        if ($this->getBudgetForSubCategories()->contains($budget)) {
            $this->getBudgetForSubCategories()->removeElement($budget);
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

    public function addBudgetForSubCategory(BudgetForSubCategory $subCategory)
    {
        $subCategory->setBudget($this);
        $this->budgetForSubCategories[] = $subCategory;

        return $this;
    }

    public function removeBudgetForSubCategory(BudgetForSubCategory $subCategory)
    {
        $this->budgetForSubCategories->removeElement($subCategory);
    }

    public function getBudgetForSubCategories()
    {
        return $this->budgetForSubCategories;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return Budget
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
     * @return Budget
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
     * Set month
     *
     * @param Date $month
     * @return Budget
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return Date
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return ArrayCollection
     */
    public function getAdditionalBudgetForSubCategories()
    {
        return $this->additionalBudgetForSubCategories;
    }

    /**
     * @param ArrayCollection $additionalBudgetForSubCategories
     */
    public function setAdditionalBudgetForSubCategories($additionalBudgetForSubCategories)
    {
        $this->additionalBudgetForSubCategories = $additionalBudgetForSubCategories;
    }
}
