<?php

namespace Solustat\TimeSheetBundle\Listener;

use Solustat\TimeSheetBundle\Event\CalendarEvent as EventCalendarEvent;
use Solustat\TimeSheetBundle\Entity\CalendarEvent as FullCalendarEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


class LoadDataListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $em;
    protected $userCurrent;
    protected $startDate;
    protected $endDate;
    protected $filters;

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
        $this->userCurrent = $this->container->get('security.token_storage')->getToken()->getUser();
        $this->startDate = $calendarEvent->getStart();
        $this->endDate = $calendarEvent->getEnd();
        $this->filters = $calendarEvent->getFilters();

        switch ($this->filters['action']) {
            case 'add':
                $this->addAction($calendarEvent);
                break;
            case 'update':
                $this->updateAction($calendarEvent);
                break;
            default:
                $this->loadAction($calendarEvent);
        }
    }

    private function addAction(EventCalendarEvent $calendarEvent)
    {
        $event = $this->em->getRepository('SolustatTimeSheetBundle:Event')->insertEvent(
            $this->userCurrent,
            $this->startDate,
            $this->endDate,
            $this->filters
        );

        $calendarEvent->addEvent(new FullCalendarEvent(
            $event->getId(),
            $event->getTitle(),
            $event->getVisitDate(),
            $event->getPatient(),
            $event->getVisitTime()
        ));
    }

    private function updateAction(EventCalendarEvent $calendarEvent)
    {
        $event = $this->em->getRepository('SolustatTimeSheetBundle:Event')->updateEvent(
            $this->userCurrent,
            $this->startDate,
            $this->endDate,
            $this->filters
        );

        $calendarEvent->addEvent(new FullCalendarEvent(
            $event->getId(),
            $event->getTitle(),
            $event->getVisitDate(),
            $event->getPatient(),
            $event->getVisitTime()
        ));

    }

    private function loadAction(EventCalendarEvent $calendarEvent)
    {
        $events = $this->em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->where('e.visitDate >= :startDate')
            ->andWhere('e.visitDate <= :endDate')
            ->andWhere('e.user = :id')
            ->andWhere('e.linked = :linked')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->setParameter('id', $this->userCurrent->getId())
            ->setParameter('linked',1)
            ->getQuery()
            ->execute();

        foreach ($events as $event) {
            $calendarEvent->addEvent(new FullCalendarEvent(
                $event->getId(),
                $event->getTitle(),
                $event->getVisitDate(),
                $event->getPatient(),
                $event->getVisitTime()
            ));
        }
    }
}