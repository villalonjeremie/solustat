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
                $this->addAction();
                break;
            case 'update':
                $this->updateAction();
                break;
            case 'delete':
                $this->deleteAction();
                break;
            default:
                $this->loadAction($calendarEvent);
        }
    }

    private function addAction()
    {
        $this->em->getRepository('SolustatTimeSheetBundle:Event')->insertEvent(
            $this->userCurrent,
            $this->startDate,
            $this->endDate,
            $this->filters
        );
    }

    private function updateAction()
    {
        $this->em->getRepository('SolustatTimeSheetBundle:Event')->insertEvent(
            $this->userCurrent,
            $this->startDate,
            $this->endDate,
            $this->filters
        );

    }

    private function deleteAction()
    {
        $this->em->getRepository('SolustatTimeSheetBundle:Event')->deleteEvent(
            $this->userCurrent,
            $this->startDate,
            $this->endDate,
            $this->filters
        );

    }

    private function loadAction(EventCalendarEvent $calendarEvent)
    {
        $events = $this->em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->where('e.visitDate >= :startDate')
            ->andWhere('e.visitDate <= :endDate')
            ->andWhere('e.user = :id')
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->setParameter('id', $this->userCurrent->getId())
            ->getQuery()
            ->execute();

        foreach ($events as $event) {
            $calendarEvent->addEvent(new FullCalendarEvent(
                $event->getTitle(),
                $event->getVisitDate(),
                $event->getPatient()
            ));
        }
    }
}