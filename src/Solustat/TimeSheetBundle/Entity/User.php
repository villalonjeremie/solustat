<?php
namespace Solustat\TimeSheetBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
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
     * @ORM\Column(name="name", type="string", length=255)
     *
     */
    protected $name;

    /**
     * @ORM\Column(name="surname", type="string", length=255)
     */
    protected $surname;

    /**
     * @ORM\Column(name="registration_nb", type="string", length=255)
     */
    protected $registrationNb;

    /**
     * @ORM\Column(name="tel_work", type="string", length=255)
     */
    protected $telWork;

    /**
     * @ORM\Column(name="tel_mobile", type="string", length=255)
     */
    protected $telMobile;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Sector", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $sector;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Position", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $position;

    public function __construct()
    {
        parent::__construct();
        $this->createdAt   = new \Datetime();
        $this->roles = array('ROLE_USER');
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
     * Set surname
     *
     * @param string $surname
     *
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set registrationNb
     *
     * @param string $registrationNb
     *
     * @return User
     */
    public function setRegistrationNb($registrationNb)
    {
        $this->registrationNb = $registrationNb;

        return $this;
    }

    /**
     * Get registrationNb
     *
     * @return string
     */
    public function getRegistrationNb()
    {
        return $this->registrationNb;
    }

    /**
     * Set telWork
     *
     * @param string $telWork
     *
     * @return User
     */
    public function setTelWork($telWork)
    {
        $this->telWork = $telWork;

        return $this;
    }

    /**
     * Get telWork
     *
     * @return string
     */
    public function getTelWork()
    {
        return $this->telWork;
    }

    /**
     * Set telMobile
     *
     * @param string $telMobile
     *
     * @return User
     */
    public function setTelMobile($telMobile)
    {
        $this->telMobile = $telMobile;

        return $this;
    }

    /**
     * Get telMobile
     *
     * @return string
     */
    public function getTelMobile()
    {
        return $this->telMobile;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set sector
     *
     * @param \Solustat\TimeSheetBundle\Entity\Sector $sector
     *
     * @return User
     */
    public function setSector(\Solustat\TimeSheetBundle\Entity\Sector $sector)
    {
        $this->sector = $sector;

        return $this;
    }

    /**
     * Get sector
     *
     * @return \Solustat\TimeSheetBundle\Entity\Sector
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * Set position
     *
     * @param \Solustat\TimeSheetBundle\Entity\Position $position
     *
     * @return User
     */
    public function setPosition(\Solustat\TimeSheetBundle\Entity\Position $position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return \Solustat\TimeSheetBundle\Entity\Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $email
     * @return $this|static
     */
    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }
}
