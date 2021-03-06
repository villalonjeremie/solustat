<?php

namespace Solustat\TimeSheetBundle\Entity;

use Solustat\TimeSheetBundle\Model\FullCalendarEvent;

class CalendarEvent extends FullCalendarEvent
{
    public function toArray()
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'start' => $this->startDate->format('Y-m-d\TH:i:s.u'),
            'end'   => $this->endDate->format('Y-m-d\TH:i:s.u'),
            'color' => $this->patient->getTypeCare()->getColor(),
            'time'  => $this->visitTime->getTimestamp()
        ];
    }
}
