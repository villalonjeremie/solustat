<?php

namespace Solustat\TimeSheetBundle\Listener;

use AncaRebeca\FullCalendarBundle\Event\CalendarEvent as EventCalendarEvent;
use Solustat\TimeSheetBundle\Entity\CalendarEvent as FullCalendarEvent;

class LoadDataListener
{
    /**
     * @param EventCalendarEvent $calendarEvent
     *
     */
    public function loadData(EventCalendarEvent $calendarEvent)
    {
        $startDate = $calendarEvent->getStart();
        $endDate = $calendarEvent->getEnd();
        $filters = $calendarEvent->getFilters();

        //You may want do a custom query to populate the events

        $calendarEvent->addEvent(new FullCalendarEvent('Event Title 1', new \DateTime('now',new \DateTimeZone('America/Montreal'))));
        $calendarEvent->addEvent(new FullCalendarEvent('Event Title 2', new \DateTime('now',new \DateTimeZone('America/Montreal'))));
    }
}