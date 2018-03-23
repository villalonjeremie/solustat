<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_frequency")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\FrequencyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Frequency
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="nb_repetition", type="integer", nullable=false)
     */
    private $nbRepetition;

    /**
     * @ORM\Column(name="nb_rep_per_time", type="integer", nullable=false)
     */
    private $nbRepPerTime;

    /**
     * @ORM\Column(name="time", type="string", nullable=false)
     */
    private $time;

    /**
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\Patient",mappedBy="frequency")
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
     * Set nbRepetition
     *
     * @param integer $nbRepetition
     *
     * @return Frequency
     */
    public function setNbRepetition($nbRepetition)
    {
        $this->nbRepetition = $nbRepetition;

        return $this;
    }

    /**
     * Get nbRepetition
     *
     * @return integer
     */
    public function getNbRepetition()
    {
        return $this->nbRepetition;
    }

    /**
     * Set nbRepPerTime
     *
     * @param integer $nbRepPerTime
     *
     * @return Frequency
     */
    public function setNbRepPerTime($nbRepPerTime)
    {
        $this->nbRepPerTime = $nbRepPerTime;

        return $this;
    }

    /**
     * Get nbRepPerTime
     *
     * @return integer
     */
    public function getNbRepPerTime()
    {
        return $this->nbRepPerTime;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return Frequency
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Add patient
     *
     * @param \Solustat\TimeSheetBundle\Entity\Patient $patient
     *
     * @return Frequency
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
