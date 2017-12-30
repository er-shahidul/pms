<?php

namespace Pms\SettingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseType
 *
 * @ORM\Table(name="purchase_type")
 * @ORM\Entity(repositoryClass="Pms\SettingBundle\Entity\Repository\PurchaseTypeRepository")
 */
class PurchaseType
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
     * @ORM\OneToMany(targetEntity="Pms\CoreBundle\Entity\PurchaseOrder", mappedBy="poNonpo", cascade={"persist", "remove"})
     */
    private $purchaseOrder;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Pms\SettingBundle\Entity\TermsAndConditions", mappedBy="sector", cascade={"persist", "remove"})
     */
    private $termsAndConditions;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_name", type="string", length=255)
     */
    private $shortName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status;

    public function __construct() {

        if(!$this->getId()){
            $this->setStatus(true);
        }
    }


    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTermsAndConditions()
    {
        return $this->termsAndConditions;
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
     * Set name
     *
     * @param string $name
     * @return PurchaseType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set shortName
     *
     * @param string $shortName
     * @return PurchaseType
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
