<?php

namespace Pms\SupplierBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Category;
use Pms\SettingBundle\Entity\Item;
use Pms\SettingBundle\Entity\ItemType;
use Pms\SettingBundle\Entity\SubCategory;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Supplier
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Pms\SupplierBundle\Entity\Repository\SupplierRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Supplier
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="contactPerson", type="string", length=255)
     */
    private $contactPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_no", type="string", length=255)
     */
    private $contactNo;
    /**
     * @var string
     *
     * @ORM\Column(name="item_name", type="string", length=255)
     */
    private $item;

    /**
     * @var string
     *
     * @ORM\Column(name="specification", type="string", length=255)
     */
    private $specification;

    /**
     * @var string
     *
     * @ORM\Column(name="brand", type="string", length=255)
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="remark", type="text", nullable=true)
     */
    private $remark;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\Category", inversedBy="suppliers")
     * @ORM\JoinColumn(name="categories", nullable=true)
     */
    private $category;

    /**
     * @var SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\SubCategory", inversedBy="suppliers")
     * @ORM\JoinColumn(name="sub_categories", nullable=true)
     */
    private $subCategory;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $createdBy;

    /**
     * @var ItemType
     *
     * @ORM\ManyToOne(targetEntity="Pms\SettingBundle\Entity\ItemType")
     */
    private $itemType;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SupplierDocument", mappedBy="supplier", cascade={"persist", "remove"})
     */
    private $documents;


    /**
     * @var
     */
    private $files;

    /**
     * Supplier constructor.
     */
    public function __construct()
    {
        $this->documents = new ArrayCollection();
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
     * @return Supplier
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
     * Set contactPerson
     *
     * @param string $contactPerson
     * @return Supplier
     */
    public function setContactPerson($contactPerson)
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    /**
     * Get contactPerson
     *
     * @return string 
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Supplier
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set remark
     *
     * @param string $remark
     * @return Supplier
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;

        return $this;
    }

    /**
     * Get remark
     *
     * @return string 
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return Supplier
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
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }


    /**
     * @param $document
     */
    public function addDocument(SupplierDocument $document)
    {
        $this->documents->add($document);
        $document->setSupplier($this);
    }

    /**
     * @return ItemType
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param ItemType $itemType
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFiles()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error



            foreach($this->getFiles() as $file) {
                /** @var UploadedFile $file */
              if($file !=null){
                $name = sha1(uniqid(mt_rand(), true)).".".$file->guessExtension();
                $file->move($this->getUploadRootDir(), $name);
                $this->addDocument(new SupplierDocument($name));
              }
            }

    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/file';
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function addFile(UploadedFile $file = null)
    {
        $this->files[] = $file;
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @param string $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getContactNo()
    {
        return $this->contactNo;
    }

    /**
     * @param string $contactNo
     */
    public function setContactNo($contactNo)
    {
        $this->contactNo = $contactNo;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param string $item
     */
    public function setItem($item)
    {
        $this->item = $item;
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


}
