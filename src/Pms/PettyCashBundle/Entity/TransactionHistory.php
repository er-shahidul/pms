<?php

namespace Pms\PettyCashBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TransactionHistory
 *
 * @ORM\Table(name="transaction_history")
 * @ORM\Entity(repositoryClass="Pms\PettyCashBundle\Entity\Repository\TransactionHistoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TransactionHistory
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
     * @var float
     *
     * @ORM\Column(name="transaction_history_amount", type="float")
     */
    private $transactionHistoryAmount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text")
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_type", type="string", length=25)
     */
    private $transactionType;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20)
     */
    private $status;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pms\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", nullable=true)
     */
    private $createdBy;

    /**
     * @var Transaction
     *
     * @ORM\ManyToOne(targetEntity="Pms\PettyCashBundle\Entity\Transaction", inversedBy="transactionHistory",cascade={"persist", "remove"})
     */
    private $transaction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pathTwo;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pathThree;
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $fileTwo;
    /**
     * @Assert\File(maxSize="6000000")
     */
    private $fileThree;


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
     * Set transactionHistoryAmount
     *
     * @param float $transactionHistoryAmount
     * @return TransactionHistory
     */
    public function setTransactionHistoryAmount($transactionHistoryAmount)
    {
        $this->transactionHistoryAmount = $transactionHistoryAmount;

        return $this;
    }

    /**
     * Get transactionHistoryAmount
     *
     * @return float 
     */
    public function getTransactionHistoryAmount()
    {
        return $this->transactionHistoryAmount;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return TransactionHistory
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
     * Set notes
     *
     * @param string $notes
     * @return TransactionHistory
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set transactionType
     *
     * @param string $transactionType
     * @return TransactionHistory
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Get transactionType
     *
     * @return string 
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return TransactionHistory
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
    public function getAbsolutePathThree()
    {
        return null === $this->pathThree
            ? null
            : $this->getUploadRootDir().'/'.$this->pathThree;
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
    public function getWebPathThree()
    {
        return null === $this->pathThree
            ? null
            : $this->getUploadDir().'/'.$this->pathThree;
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
        return 'uploads/PettyCashDocuments';
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
    public function uploadThree()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFileThree()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFileThree()->move(
            $this->getUploadRootDir(),
            $this->getFileThree()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->pathThree = $this->getFileThree()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->fileThree = null;
    }

    /**
     * @return mixed
     */
    public function getPathTwo()
    {
        return $this->pathTwo;
    }
    /**
     * @return mixed
     */
    public function getPathThree()
    {
        return $this->pathThree;
    }

    /**
     * @param mixed $pathTwo
     */
    public function setPathTwo($pathTwo)
    {
        $this->pathTwo = $pathTwo;
    }
    /**
     * @param mixed $pathThree
     */
    public function setPathThree($pathThree)
    {
        $this->pathThree = $pathThree;
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
     * Get fileThree.
     *
     * @return UploadedFile
     */
    public function getFileThree()
    {
        return $this->fileThree;
    }

    /**
     * Sets fileThree.
     *
     * @param UploadedFile $fileThree
     */
    public function setFileThree(UploadedFile $fileThree = null)
    {
        $this->fileThree = $fileThree;
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

    public function getAbsolutePathByIndex($index) {
        switch($index) {
            case 1: return $this->getAbsolutePath();
            case 2: return $this->getAbsolutePathTwo();
            case 3: return $this->getAbsolutePathThree();
        }
    }

    public function getWebPathByIndex($index) {
        switch($index) {
            case 1: return $this->getWebPath();
            case 2: return $this->getWebPathTwo();
            case 3: return $this->getWebPathThree();
        }
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }
}
