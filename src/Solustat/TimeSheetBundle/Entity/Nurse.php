<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_nurse")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\NurseRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Nurse
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(name="post", type="string", length=255)
     */
    private $post;

    /**
     * @ORM\Column(name="registration_nb", type="string", length=255)
     */
    private $registrationNb;

    /**
     * @ORM\Column(name="security_level", type="string", length=255)
     */
    private $securityLevel;

    /**
     * @ORM\Column(name="tel_work", type="string", length=255)
     */
    private $telWork;

    /**
     * @ORM\Column(name="tel_mobile", type="string", length=255)
     */
    private $telMobile;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Sector", inversedBy="nurses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sector;

    public function __construct()
    {
        $this->createdAt   = new \DateTime('now');
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedAt(new \DateTime('now'));
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
     *
     * @return Nurse
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
     * @return Nurse
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
     * Set post
     *
     * @param string $post
     *
     * @return Nurse
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return string
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set registrationNb
     *
     * @param string $registrationNb
     *
     * @return Nurse
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
     * Set securityLevel
     *
     * @param integer $securityLevel
     *
     * @return Nurse
     */
    public function setSecurityLevel($securityLevel)
    {
        $this->securityLevel = $securityLevel;

        return $this;
    }

    /**
     * Get securityLevel
     *
     * @return integer
     */
    public function getSecurityLevel()
    {
        return $this->securityLevel;
    }

    /**
     * Set telWork
     *
     * @param string $telWork
     *
     * @return Nurse
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
     * @return Nurse
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Nurse
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
     * @return Nurse
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Nurse
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
}
