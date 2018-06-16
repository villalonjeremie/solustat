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
     * @ORM\Column(name="visit_date", type="datetime", nullable=true)
     */
    private $visitDate;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\Patient", inversedBy="events", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\User", inversedBy="events", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Solustat\TimeSheetBundle\Entity\VisitTime", inversedBy="events", cascade={"persist"})
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

    /**
     * @ORM\Column(name="auto_generate", type="boolean", nullable=false)
     */
    private $autoGenerate;

    /**
     * @ORM\Column(name="linked", type="boolean", nullable=false)
     */
    private $linked;

    public function __construct()
    {
        $this->createdAt   = new \Datetime();
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
     * Set visitDate
     *
     * @param \DateTime $visitDate
     *
     * @return Event
     */
    public function setVisitDate($visitDate)
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    /**
     * Get visitDate
     *
     * @return \DateTime
     */
    public function getVisitDate()
    {
        return $this->visitDate;
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
     * Set user
     *
     * @param \Solustat\TimeSheetBundle\Entity\User $user
     *
     * @return Event
     */
    public function setUser(\Solustat\TimeSheetBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Solustat\TimeSheetBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
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

    /**
     * Set autoGenerate
     *
     * @param boolean $autoGenerate
     *
     * @return Event
     */
    public function setAutoGenerate($autoGenerate)
    {
        $this->autoGenerate = $autoGenerate;

        return $this;
    }

    /**
     * Get autoGenerate
     *
     * @return boolean
     */
    public function getAutoGenerate()
    {
        return $this->autoGenerate;
    }

    /**
     * Set linked
     *
     * @param boolean $linked
     *
     * @return Event
     */
    public function setLinked($linked)
    {
        $this->linked = $linked;

        return $this;
    }

    /**
     * Get linked
     *
     * @return boolean
     */
    public function getLinked()
    {
        return $this->linked;
    }
}
