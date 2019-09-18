<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_position")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\PositionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Position
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="position", type="string", length=255)
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\User",mappedBy="position")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set position
     *
     * @param string $position
     *
     * @return Position
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Add user
     *
     * @param \Solustat\TimeSheetBundle\Entity\User $user
     *
     * @return Position
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
