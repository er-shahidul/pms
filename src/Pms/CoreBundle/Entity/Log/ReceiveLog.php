<?php

namespace Pms\CoreBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Pms\CoreBundle\Entity\Receive;
use Pms\UserBundle\Entity\User;

/**
 * receiveLog
 *
 * @ORM\Table(name="receive_log")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\Log\ReceiveLogRepository")
 */
class ReceiveLog
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
     * @var Receive
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\Receive", inversedBy="receiveLogs")
     * @ORM\JoinColumn(name="receive")
     */
    private $receive;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by")
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

    public function __construct()
    {
        $this->createdDate = new \DateTime();
    }

    /**
     * Set receive
     *
     * @param Receive $receive
     * @return ReceiveLog
     */
    public function setReceive($receive)
    {
        $this->receive = $receive;

        return $this;
    }

    /**
     * Get receive
     *
     * @return Receive
     */
    public function getReceive()
    {
        return $this->receive;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     * @return ReceiveLog
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
     * @return ReceiveLog
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
     * @return ReceiveLog
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
