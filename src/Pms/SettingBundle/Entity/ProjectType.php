<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * ProjectType
 *
 * @ORM\Table(name="project_types")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\ProjectTypeRepository")
 */
class ProjectType
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
     * @ORM\Column(name="projects_category_name", type="string", length=255)
     */
    private $projectCategoryName;

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
     * Set projectCategoryName
     *
     * @param string $projectCategoryName
     * @return ProjectType
     */
    public function setProjectCategoryName($projectCategoryName)
    {
        $this->projectCategoryName = $projectCategoryName;

        return $this;
    }

    /**
     * Get projectCategoryName
     *
     * @return string
     */
    public function getProjectCategoryName()
    {
        return $this->projectCategoryName;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return ProjectType
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
     * @return ProjectType
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
     * @return ProjectType
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
}
