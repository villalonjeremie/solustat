<?php

namespace Solustat\TimeSheetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="solustat_type_care")
 * @ORM\Entity(repositoryClass="Solustat\TimeSheetBundle\Repository\TypeCareRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TypeCare
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
     * @ORM\Column(name="color", type="string", length=255)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="Solustat\TimeSheetBundle\Entity\Patient",mappedBy="typeCare")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patients;

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
     * @return TypeCare
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
     * Set color
     *
     * @param string $color
     *
     * @return TypeCare
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
}
