<?php

namespace Solustat\TimeSheetBundle\Listener;

use AncaRebeca\FullCalendarBundle\Event\CalendarEvent as EventCalendarEvent;
use Solustat\TimeSheetBundle\Entity\CalendarEvent as FullCalendarEvent;
use Doctrine\ORM\EntityManager;

class LoadDataListener
{
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param EventCalendarEvent $calendarEvent
     *
     */
    public function loadData(EventCalendarEvent $calendarEvent)
    {
        $startDate = $calendarEvent->getStart();
        $endDate = $calendarEvent->getEnd();
        $filters = $calendarEvent->getFilters();

        $calendarEvent->addEvent(new FullCalendarEvent('Event Title 1', new \DateTime('now',new \DateTimeZone('America/Montreal'))));
        $calendarEvent->addEvent(new FullCalendarEvent('Event Title 2', new \DateTime('now',new \DateTimeZone('America/Montreal'))));


        $q = $this->em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->where('e.visitDate >= :startDate')
            ->andWhere('e.visitDate <= :endDate')
            ->andWhere('e.user = :id')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('id', 1)
            ->getQuery()
            ->execute();


        $test = 'coujcoi';
//
//        foreach ($events as $event) {
//            $calendarEvent->addEvent(new FullCalendarEvent($event->getTitle(), $event->getVisitDate()));
//        }
    }
}