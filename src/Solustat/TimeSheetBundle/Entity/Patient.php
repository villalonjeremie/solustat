<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_patient")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\PatientRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Patient
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="folder_number", type="integer",unique=true)
     */
    private $folderNumber;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(name="zip", type="string", length=255)
     */
    private $zip;

    /**
     * @ORM\Column(name="town", type="string", length=255)
     */
    private $town;

    /**
     * @ORM\Column(name="tel", type="string", length=255)
     */
    private $tel;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Sector", inversedBy="patients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sector;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\VisitTime", inversedBy="patients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $visitTime;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\TypeCare", inversedBy="patients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeCare;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Frequency", inversedBy="patients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $frequency;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;


    public function __construct()
    {
        $this->dateCreate   = new \Datetime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function createDate()
    {
        $this->setCreatedAt(new \Datetime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedAt(new \Datetime());
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
     * Set folderNumber
     *
     * @param integer $folderNumber
     *
     * @return Patient
     */
    public function setFolderNumber($folderNumber)
    {
        $this->folderNumber = $folderNumber;

        return $this;
    }

    /**
     * Get folderNumber
     *
     * @return integer
     */
    public function getFolderNumber()
    {
        return $this->folderNumber;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Patient
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Patient
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
     * @return Patient
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
     * Set address
     *
     * @param string $address
     *
     * @return Patient
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Patient
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set town
     *
     * @param string $town
     *
     * @return Patient
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set tel
     *
     * @param string $tel
     *
     * @return Patient
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Get tel
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Patient
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
     * @return Patient
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
     * Set visitTime
     *
     * @param \Solustat\TimeSheetBundle\Entity\VisitTime $visitTime
     *
     * @return Patient
     */
    public function setVisitTime(\Solustat\TimeSheetBundle\Entity\VisitTime $visitTime)
    {
        $this->visitTime = $visitTime;

        return $this;
    }

    /**
     * Get visitTime
     *
     * @return \Solustat\TimeSheetBundle\Entity\VisitTime
     */
    public function getVisitTime()
    {
        return $this->visitTime;
    }

    /**
     * Set typeCare
     *
     * @param \Solustat\TimeSheetBundle\Entity\TypeCare $typeCare
     *
     * @return Patient
     */
    public function setTypeCare(\Solustat\TimeSheetBundle\Entity\TypeCare $typeCare)
    {
        $this->typeCare = $typeCare;

        return $this;
    }

    /**
     * Get typeCare
     *
     * @return \Solustat\TimeSheetBundle\Entity\TypeCare
     */
    public function getTypeCare()
    {
        return $this->typeCare;
    }

    /**
     * Set frequency
     *
     * @param \Solustat\TimeSheetBundle\Entity\Frequency $frequency
     *
     * @return Patient
     */
    public function setFrequency(\Solustat\TimeSheetBundle\Entity\Frequency $frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency
     *
     * @return \Solustat\TimeSheetBundle\Entity\Frequency
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Patient
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
