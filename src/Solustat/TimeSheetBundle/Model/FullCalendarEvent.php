<?php

namespace Solustat\TimeSheetBundle\Model;

use Solustat\TimeSheetBundle\Entity\Patient;
use Solustat\TimeSheetBundle\Entity\VisitTime;

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
     * @var \DateTime|false|string
     */
    protected $endDate;

    /**
     * @var VisitTime
     */
    protected $visitTime;

    /**
     * FullCalendarEvent constructor.
     * @param $id
     * @param $title
     * @param \DateTime $start
     * @param Patient $patient
     * @param VisitTime $visitTime
     */
    public function __construct($id, $title, \DateTime $start, Patient $patient, VisitTime $visitTime)
    {
        $this->id = $id;
        $this->title = $title;
        $this->startDate = $start;
        $timestamp = $start->getTimestamp() + $visitTime->getTimestamp();
        $this->endDate = \DateTime::createFromFormat('U', $timestamp)->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $this->patient = $patient;
        $this->visitTime = $visitTime;
    }

    /**
     * @return array
     */
    abstract public function toArray();
}