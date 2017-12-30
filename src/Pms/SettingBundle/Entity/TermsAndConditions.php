<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * TermsAndConditions
 *
 * @ORM\Table(name="terms_and_conditions")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\TermsAndConditionsRepository")
 */
class TermsAndConditions
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
     * @ORM\Column(name="conditions", type="string", length=255)
     */
    private $condition;

    /**
     * @var PurchaseType
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\PurchaseType", inversedBy="termsAndConditions")
     * @ORM\JoinColumn(name="sectors", nullable=true)
     */
    private $sector;

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
     * Set condition
     *
     * @param string $condition
     * @return TermsAndConditions
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set sector
     *
     * @param PurchaseType $sector
     * @return TermsAndConditions
     */
    public function setSector($sector)
    {
        $this->sector = $sector;

        return $this;
    }

    /**
     * Get PurchaseType
     *
     * @return string
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return TermsAndConditions
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
     * @return TermsAndConditions
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
     * @return TermsAndConditions
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
