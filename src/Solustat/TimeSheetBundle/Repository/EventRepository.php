<?php
namespace Solustat\TimeSheetBundle\Repository;

use Solustat\TimeSheetBundle\Entity\Event;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityRepository;

/**
 * EventRepository
 *
 */
class EventRepository extends EntityRepository
{
    public function insertBulkEvents(array $entities)
    {
        $patient = $entities['patient'];
        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $entities['frequency']);
        $arraySize = count($arrayEvents);

        echo "Memory usage before: " . (memory_get_usage() / 1024) . " KB" . PHP_EOL;
        $s = microtime(true);
        $batchSize = 10;

        for ($i=1; $i<=$arraySize; ++$i) {
            $event = new Event();
            $event->setTitle($patient->getName().' '.$patient->getSurname());
            $event->setVisitDate(new \Datetime($patient->getStartingDate()));
            $event->setCreatedAt(new \Datetime());
            $event->setPatient($entities['patient']);
            $event->setUser($entities['user']);
            $event->setVisitTime($entities['visit_time']);
            $this->_em->persist($event);

            if (($i % $batchSize) == 0) {
                try {
                    $this->_em->flush();
                    $this->_em->clear();
                } catch (ORMException $e) {
                    throw new NoSuchMetadataException($e);
                }
            }
        }

        $this->_em->flush();
        $this->_em->clear();

        echo "Memory usage after: " . (memory_get_usage() / 1024) . " KB" . PHP_EOL;

        $e = microtime(true);
        echo ' Inserted'.$arraySize.' objects in ' . ($e - $s) . ' seconds' . PHP_EOL;

    }

    private function getArrayEvents(\DateTime $startingDate, \Solustat\TimeSheetBundle\Entity\Frequency $frequency)
    {
        $result = [];
        $startWeekNumber = $startingDate->format('W');
        $year = $startingDate->format('Y');

        $weekCountPlentyYear = date('W', strtotime( $year . '-12-31'));

        if ($weekCountPlentyYear == '01') {
            $weekCountPlentyYear = date('W', strtotime($year . '-12-24'));
        }

        $weekRemainingThisYear = (int)$weekCountPlentyYear + 1 - (int)$startWeekNumber;
        $weekRemainingNextYear = (int)$startWeekNumber - 1;

        for ($i = (int) $startWeekNumber; $i <= $weekRemainingThisYear; ++$i) {
            if ($frequency->getTime() === 'day')
            {
                for ($i=1; $i <= $arraySize; ++$i) {
                $result[] = [

                ];
            }
        }

        die;
        return $result;

    }


}
