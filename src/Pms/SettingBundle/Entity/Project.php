<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * Project
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\ProjectRepository")
 */
class Project
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
     * @ORM\Column(name="projects_name", type="string", length=255)
     */
    private $projectName;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="cost_center_number", type="string", length=255)
     */
    private $costCenterNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisition", mappedBy="project")
     */
    private $purchaseRequisition;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\Delivery", mappedBy="project")
     */
    private $delivery;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\ReceivedItem", mappedBy="project")
     */
    private $receivedItem;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\TotalReceiveItem", mappedBy="project")
     */
    private $totalReceiveItem;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\DailyInventory", mappedBy="project")
     */
    private $dailyInventory;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\BudgetBundle\Entity\Budget", mappedBy="project")
     */
    private $budget;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrderItem", mappedBy="project")
     */
    private $purchaseOrderItem;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="project_heads", nullable=true)
     */
    private $projectHead;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="project_contact_persons", nullable=true)
     */
    private $projectContactPerson;

    /**
     * @var Area
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Area")
     * @ORM\JoinColumn(name="projects_area", nullable=true)
     */
    private $projectArea;

    /**
     * @var ProjectType
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\ProjectType")
     * @ORM\JoinColumn(name="project_types", nullable=true)
     */
    private $projectCategory;

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
     * @ORM\ManyToMany(targetEntity="Pms\UserBundle\Entity\User", mappedBy="projects")
     **/
    protected $users;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_head_office", type="boolean")
     */
    private $isHeadOffice = false;


    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function geReceivedItem()
    {
        return $this->receivedItem;
    }

    public function addUser(User $user)
    {
        $user->addProject($this);

        if (!$this->getUsers()->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user)
    {
        if ($this->getUsers()->contains($user)) {
            $this->getUsers()->removeElement($user);
        }
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
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
     * Set projectHead
     *
     * @param User $projectHead
     * @return Project
     */
    public function setProjectHead($projectHead)
    {
        $this->projectHead = $projectHead;

        return $this;
    }

    /**
     * Get projectHead
     *
     * @return User
     */
    public function getProjectHead()
    {
        return $this->projectHead;
    }

    /**
     * Set projectContactPerson
     *
     * @param User $projectContactPerson
     * @return Project
     */
    public function setProjectContactPerson($projectContactPerson)
    {
        $this->projectContactPerson = $projectContactPerson;

        return $this;
    }

    /**
     * Get projectContactPerson
     *
     * @return User
     */
    public function getProjectContactPerson()
    {
        return $this->projectContactPerson;
    }

    /**
     * Set projectArea
     *
     * @param Area $projectArea
     * @return Project
     */
    public function setProjectArea($projectArea)
    {
        $this->projectArea = $projectArea;

        return $this;
    }

    /**
     * Get projectArea
     *
     * @return Area
     */
    public function getProjectArea()
    {
        return $this->projectArea;
    }

    /**
     * Set projectCategory
     *
     * @param ProjectType $projectCategory
     * @return Project
     */
    public function setProjectCategory($projectCategory)
    {
        $this->projectCategory = $projectCategory;

        return $this;
    }

    /**
     * Get projectCategory
     *
     * @return ProjectType
     */
    public function getProjectCategory()
    {
        return $this->projectCategory;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Project
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set costCenterNumber
     *
     * @param string $costCenterNumber
     * @return Project
     */
    public function setCostCenterNumber($costCenterNumber)
    {
        $this->costCenterNumber = $costCenterNumber;

        return $this;
    }

    /**
     * Get costCenterNumber
     *
     * @return string
     */
    public function getCostCenterNumber()
    {
        return $this->costCenterNumber;
    }

    /**
     * Set projectName
     *
     * @param string $projectName
     * @return Project
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    /**
     * Get projectName
     *
     * @return User
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return Project
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return Project
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
     * @return Project
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
    public function getPurchaseRequisition()
    {
        return $this->purchaseRequisition;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseOrderItem()
    {
        return $this->purchaseOrderItem;
    }

    /**
     * @return ArrayCollection
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param ArrayCollection $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return ArrayCollection
     */
    public function getTotalReceiveItem()
    {
        return $this->totalReceiveItem;
    }

    /**
     * @return boolean
     */
    public function isIsHeadOffice()
    {
        return $this->isHeadOffice;
    }

    /**
     * @param boolean $isHeadOffice
     */
    public function setIsHeadOffice($isHeadOffice)
    {
        $this->isHeadOffice = $isHeadOffice;
    }

    /**
     * @return ArrayCollection
     */
    public function getDailyInventory()
    {
        return $this->dailyInventory;
    }
}
