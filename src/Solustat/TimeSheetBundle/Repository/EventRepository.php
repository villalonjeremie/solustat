<?php

namespace Solustat\TimeSheetBundle\Repository;
use Solustat\TimeSheetBundle\Entity\Event;

/**
 * EventRepository
 *
 */
class EventRepository extends \Doctrine\ORM\EntityRepository
{

    public function insertBulkEvents(array $arrayEvents, array $entities)
    {
        $arraySize = count($arrayEvents);



        echo "Memory usage before: " . (memory_get_usage() / 1024) . " KB" . PHP_EOL;
        $s = microtime(true);
        $batchSize = 10;


        for ($i=1; $i<=$arraySize; ++$i) {
            $event = new Event();
            $event->setTitle('test');
            $event->setStartingDate(new \Datetime('2018-10-02'));
            $event->setCreatedAt(new \Datetime());
            $event->setPatient($entities['patient']);
            $event->setUser($entities['user']);
            $event->setVisitTime($entities['visit_time']);

            $this->_em->persist($event);

            $this->_em->flush();

            die(var_dump($arraySize));


            if (($i % $batchSize) == 0) {
                $this->_em->flush();
                $this->_em->clear();
            }

        }
        $this->_em->flush();
        $this->_em->clear();

        echo "Memory usage after: " . (memory_get_usage() / 1024) . " KB" . PHP_EOL;

        $e = microtime(true);
        echo ' Inserted'.$arraySize.' objects in ' . ($e - $s) . ' seconds' . PHP_EOL;

    }
}
