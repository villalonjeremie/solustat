<?php

namespace Solustat\TimeSheetBundle\Entity;

use AncaRebeca\FullCalendarBundle\Model\FullCalendarEvent;

class CalendarEvent extends FullCalendarEvent
{
    public function toArray()
    {
        return [
            'title' => $this->title,
            'start' => $this->startDate->format('Y-m-d\TH:i:s.u')
        ];
    }
}
