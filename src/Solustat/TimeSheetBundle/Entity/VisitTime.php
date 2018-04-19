<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_visit_time")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\VisitTimeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class VisitTime
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
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\Patient", mappedBy="visitTime")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patients;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patients = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return VisitTime
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
     * Add patient
     *
     * @param \Solustat\TimeSheetBundle\Entity\Patient $patient
     *
     * @return VisitTime
     */
    public function addPatient(\Solustat\TimeSheetBundle\Entity\Patient $patient)
    {
        $this->patients[] = $patient;

        return $this;
    }

    /**
     * Remove patient
     *
     * @param \Solustat\TimeSheetBundle\Entity\Patient $patient
     */
    public function removePatient(\Solustat\TimeSheetBundle\Entity\Patient $patient)
    {
        $this->patients->removeElement($patient);
    }

    /**
     * Get patients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPatients()
    {
        return $this->patients;
    }
}
