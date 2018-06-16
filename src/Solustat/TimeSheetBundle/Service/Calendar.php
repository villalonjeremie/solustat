<?php

namespace Solustat\TimeSheetBundle\Service;

use Solustat\TimeSheetBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use AncaRebeca\FullCalendarBundle\Service\SerializerInterface;

class Calendar
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(SerializerInterface $serializer, EventDispatcherInterface $dispatcher)
    {
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param \Datetime $startDate
     * @param \DateTime $endDate
     * @param array $filters
     * @return string
     */
    public function getData(\Datetime $startDate, \DateTime $endDate, array $filters = [])
    {
        /** @var CalendarEvent $event */
        $event = $this->dispatcher->dispatch(
            CalendarEvent::SET_DATA,
            new CalendarEvent($startDate, $endDate, $filters)
        );

        return $this->serializer->serialize($event->getEvents());
    }
}
