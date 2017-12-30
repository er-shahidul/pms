<?php

namespace Pms\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Pms\SettingBundle\Entity\Project;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="Pms\UserBundle\Entity\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="full_name", type="string", length=200 , nullable=true)
	 */
	protected $fullName;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $avatar;

	/**
	 * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
	 * @ORM\JoinTable(name="user_user_group",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
	 * )
	 */
	protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Pms\SettingBundle\Entity\Project", inversedBy="users")
     * @ORM\JoinTable(name="user_projects",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")}
     * )
     */
    protected $projects;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="notification", type="boolean")
	 */
	protected $notification = false;

    /**
     * @var User
     *
     * @ORM\OneToMany(targetEntity="Pms\InventoryBundle\Entity\Delivery",mappedBy="createdBy")
     */
    private $delivery;

	/**
	 * @ORM\OneToOne(targetEntity="Profile", mappedBy="user", cascade={"persist"})
	 *
	 */
	protected $profile;

	/**
	 * @Assert\File()
	 */
	protected $file;

    protected $role;

    public function __construct()
    {
        parent::__construct();

        $this->projects = new ArrayCollection();
    }

    public function addProject(Project $project)
    {
        $project->addUser($this);

        if (!$this->getProjects()->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    public function removeProject(Project $project)
    {
        if ($this->getProjects()->contains($project)) {
            $this->getProjects()->removeElement($project);
        }
    }

    public function getProjects()
    {
        return $this->projects;
    }

    public function toArray($collection)
    {
        $this->setRoles($collection->toArray());
    }

    public function setRole($role)
    {
        $this->setRoles(array($role));

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        $role = $this->getRoles();

        return $role[0];
    }

    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

	/**
	 * Set name
	 *
	 * @param string $name
	 * @return User
	 */
	public function setFullName($name)
	{
		$this->fullName = $name;
		return $this;
	}

	/**
	 * Get full name
	 *
	 * @return string
	 */
	public function getFullName()
	{
		return $this->fullName;
	}

	/**
	 * @param mixed $profile
	 */
	public function setProfile($profile)
	{
		$profile->setUser($this);
		$this->profile = $profile;
	}

	/**
	 * @return mixed
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * get avatar image file name
	 *
	 * @return string
	 */
	public function getAvatar()
	{
		return $this->avatar;
	}

	/**
	 * set avatar image file name
	 */
	public function setAvatar($avatar)
	{
		$this->avatar = $avatar;
	}

	public function isSuperAdmin()
	{
		$groups = $this->getGroups();
		foreach ($groups as $group) {
			if ($group->hasRole('ROLE_SUPER_ADMIN')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return boolean
	 */
	public function getNotification()
	{
		return $this->notification;
	}

	/**
	 * @param boolean $notification
	 *
	 * @return $this
	 */
	public function setNotification($notification)
	{
		$this->notification = $notification;
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

    /**
     * @return User
     */
    public function getDelivery()
    {
        return $this->delivery;
    }
}