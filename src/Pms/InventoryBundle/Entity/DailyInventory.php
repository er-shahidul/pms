<?php

namespace Pms\InventoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Entity\Project;

/**
 * DailyInventory
 *
 * @ORM\Table(name="daily_inventory")
 * @ORM\Entity(repositoryClass="Pms\InventoryBundle\Entity\Repository\DailyInventoryRepository")
 */
class DailyInventory
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
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="dailyInventory")
     * @ORM\JoinColumn(name="project_id", nullable=true)
     */
    private $project;

    /**
     * @var item
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Item", inversedBy="dailyInventory")
     * @ORM\JoinColumn(name="item_id", nullable=true)
     */
    private $item;

    /**
     * @var float
     *
     * @ORM\Column(name="opening_quantity", type="float", nullable=true )
     */
    private $openingQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="receiving_quantity", type="float", nullable=true)
     */
    private $receivingQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="issuing_quantity", type="float",nullable=true)
     */
    private $issuingQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="closing_quantity", type="float",nullable=true)
     */
    private $closingQuantity;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return float
     */
    public function getClosingQuantity()
    {
        return $this->closingQuantity;
    }

    /**
     * @param float $closingQuantity
     */
    public function setClosingQuantity($closingQuantity)
    {
        $this->closingQuantity = $closingQuantity;
    }

    /**
     * @return float
     */
    public function getIssuingQuantity()
    {
        return $this->issuingQuantity;
    }

    /**
     * @param float $issuingQuantity
     */
    public function setIssuingQuantity($issuingQuantity)
    {
        $this->issuingQuantity = $issuingQuantity;
    }

    /**
     * @return float
     */
    public function getReceivingQuantity()
    {
        return $this->receivingQuantity;
    }

    /**
     * @param float $receivingQuantity
     */
    public function setReceivingQuantity($receivingQuantity)
    {
        $this->receivingQuantity = $receivingQuantity;
    }

    /**
     * @return float
     */
    public function getOpeningQuantity()
    {
        return $this->openingQuantity;
    }

    /**
     * @param float $openingQuantity
     */
    public function setOpeningQuantity($openingQuantity)
    {
        $this->openingQuantity = $openingQuantity;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem($item)
    {
        $this->item = $item;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


}
