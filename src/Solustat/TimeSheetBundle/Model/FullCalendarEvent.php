<?php

namespace Solustat\TimeSheetBundle\Model;

use Solustat\TimeSheetBundle\Entity\Patient;

abstract class FullCalendarEvent
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @var Patient
     */
    protected $patient;

    /**
     * FullCalendarEvent constructor.
     * @param $title
     * @param \DateTime $start
     * @param Patient $patient
     */
    public function __construct($id, $title, \DateTime $start, Patient $patient)
    {
        $this->id = $id;
        $this->title = $title;
        $this->startDate = $start;
        $this->patient = $patient;
    }

    /**
     * @return array
     */
    abstract public function toArray();
}