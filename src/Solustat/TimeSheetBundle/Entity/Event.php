<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_event")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Event
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(name="starting_date", type="datetime", nullable=true)
     */
    private $startingDate;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Patient", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Nurse", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $nurse;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\VisitTime", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $visitTime;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt   = new \Datetime();
    }

    /**
     * @ORM\PrePersist
     */
    public function createEventsIA()
    {
        die('coucou');
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedAt(new \Datetime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateEventsIA()
    {
        die('update');
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
     * Set title
     *
     * @param string $title
     *
     * @return Event
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set startingDate
     *
     * @param \DateTime $startingDate
     *
     * @return Event
     */
    public function setStartingDate($startingDate)
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    /**
     * Get startingDate
     *
     * @return \DateTime
     */
    public function getStartingDate()
    {
        return $this->startingDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Event
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
     * @return Event
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
     * Set patient
     *
     * @param \Solustat\TimeSheetBundle\Entity\Patient $patient
     *
     * @return Event
     */
    public function setPatient(\Solustat\TimeSheetBundle\Entity\Patient $patient)
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Get patient
     *
     * @return \Solustat\TimeSheetBundle\Entity\Patient
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Set nurse
     *
     * @param \Solustat\TimeSheetBundle\Entity\Nurse $nurse
     *
     * @return Event
     */
    public function setNurse(\Solustat\TimeSheetBundle\Entity\Nurse $nurse)
    {
        $this->nurse = $nurse;

        return $this;
    }

    /**
     * Get nurse
     *
     * @return \Solustat\TimeSheetBundle\Entity\Nurse
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * Set visitTime
     *
     * @param \Solustat\TimeSheetBundle\Entity\VisitTime $visitTime
     *
     * @return Event
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
}
