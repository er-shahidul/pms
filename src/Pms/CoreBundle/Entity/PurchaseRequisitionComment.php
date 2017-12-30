<?php

namespace Pms\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;

/**
 * PurchaseRequisitionComment
 *
 * @ORM\Table(name="purchase_requisition_comments")
 * @ORM\Entity(repositoryClass="Pms\CoreBundle\Entity\Repository\PurchaseRequisitionCommentRepository")
 */
class PurchaseRequisitionComment
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
     * @var PurchaseRequisition
     *
     * @ORM\ManyToOne(targetEntity="Pms\CoreBundle\Entity\PurchaseRequisition", inversedBy="purchaseRequisitionComment")
     * @ORM\JoinColumn(name="purchase_requisition")
     */
    private $purchaseRequisition;

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
     * @var text
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comment;

    /**
     * Set comment
     *
     * @param  $comment
     * @return PurchaseRequisitionComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return text
     */
    public function getComment()
    {
        return $this->comment;
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
     * Set createdBy
     *
     * @param User $createdBy
     * @return PurchaseRequisitionComment
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
     * @return PurchaseRequisitionComment
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
     * Set purchaseRequisition
     *
     * @param PurchaseRequisition $purchaseRequisition
     * @return PurchaseRequisitionComment
     */
    public function setPurchaseRequisition($purchaseRequisition)
    {
        $this->purchaseRequisition = $purchaseRequisition;

        return $this;
    }

    /**
     * Get purchaseRequisition
     *
     * @return PurchaseRequisition
     */
    public function getPurchaseRequisition()
    {
        return $this->purchaseRequisition;
    }
}
