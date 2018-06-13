<?php

namespace Solustat\TimeSheetBundle\Listener;

use AncaRebeca\FullCalendarBundle\Event\CalendarEvent as EventCalendarEvent;
use Solustat\TimeSheetBundle\Entity\CalendarEvent as FullCalendarEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


class LoadDataListener
{
    protected $container;

    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    /**
     * @param EventCalendarEvent $calendarEvent
     *
     */
    public function loadData(EventCalendarEvent $calendarEvent)
    {

        $userCurrent = $this->container->get('security.token_storage')->getToken()->getUser();
        $startDate = $calendarEvent->getStart();
        $endDate = $calendarEvent->getEnd();
        $filters = $calendarEvent->getFilters();

        $calendarEvent->addEvent(new FullCalendarEvent('Event Title 1', new \DateTime('now',new \DateTimeZone('America/Montreal'))));
        $calendarEvent->addEvent(new FullCalendarEvent('Event Title 2', new \DateTime('now',new \DateTimeZone('America/Montreal'))));


        $events = $this->em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->where('e.visitDate >= :startDate')
            ->andWhere('e.visitDate <= :endDate')
            ->andWhere('e.user = :id')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('id', $userCurrent->getId())
            ->getQuery()
            ->execute();

        foreach ($events as $event) {
            $calendarEvent->addEvent(new FullCalendarEvent($event->getTitle(), $event->getVisitDate()));
        }
    }
}