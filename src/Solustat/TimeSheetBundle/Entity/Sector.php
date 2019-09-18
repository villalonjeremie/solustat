<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_sector")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\SectorRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Sector
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
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\Patient",mappedBy="sector")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patients;

    /**
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\Nurse",mappedBy="sector")
     * @ORM\JoinColumn(nullable=false)
     */
    private $nurses;

    /**
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\User",mappedBy="sector")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patients = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nurses = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Sector
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
     * @return Sector
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

    /**
     * Add nurse
     *
     * @param \Solustat\TimeSheetBundle\Entity\Nurse $nurse
     *
     * @return Sector
     */
    public function addNurse(\Solustat\TimeSheetBundle\Entity\Nurse $nurse)
    {
        $this->nurses[] = $nurse;

        return $this;
    }

    /**
     * Remove nurse
     *
     * @param \Solustat\TimeSheetBundle\Entity\Nurse $nurse
     */
    public function removeNurse(\Solustat\TimeSheetBundle\Entity\Nurse $nurse)
    {
        $this->nurses->removeElement($nurse);
    }

    /**
     * Get nurses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNurses()
    {
        return $this->nurses;
    }
    

    /**
     * Add user
     *
     * @param \Solustat\TimeSheetBundle\Entity\User $user
     *
     * @return Sector
     */
    public function addUser(\Solustat\TimeSheetBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Solustat\TimeSheetBundle\Entity\User $user
     */
    public function removeUser(\Solustat\TimeSheetBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
