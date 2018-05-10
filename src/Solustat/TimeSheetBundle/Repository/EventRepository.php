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
        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());
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
        $first_week_no = (int) $startingDate->format('W');
        $year = (int) $startingDate->format('Y');

        $last_week_of_year_no = (int) date('W', strtotime( $year . '-12-31'));

        if ($last_week_of_year_no == 1) {
            $last_week_of_year_no = (int) date('W', strtotime($year . '-12-24'));
        }
        $week_start = new \DateTime();


        if ($frequency->getTime() === 'day' || ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 1)) {

            $rangeActualYear = range($first_week_no, $last_week_of_year_no);

            foreach ($rangeActualYear as $week_no)
            {
                $week_start->setISODate($year, $week_no);

                if ($frequency->getTime() === 'day') {
                    for ($i = 0; $i < 7; $i++) {

                        for ($i = 0; $i < $frequency->getNbRepPerTime(); $i++) {
                            $result[] = $week_start->format('Y-m-d');
                        }

                        $week_start->modify('+1 day');
                    }
                }

                if ($frequency->getTime() === 'week') {

                    if ($frequency->getNbRepetition() == 1) {
                        $week_start->setISODate($year, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 2) {
                        $week_start->setISODate($year, $week_no, 2);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 3) {
                        $week_start->setISODate($year, $week_no, 1);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 5);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 4) {
                        $week_start->setISODate($year, $week_no, 1);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 2);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 6);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 5) {
                        $week_start->setISODate($year, $week_no, 1);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 5);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($year, $week_no, 7);
                        $result[] = $week_start->format('Y-m-d');
                    }

                }

            }

            $rangeNextYear = range(1, $first_week_no);

            if (($first_week_no - 1) != 0) {
                $year = $year + 1;

                foreach ($rangeNextYear as $week_no)
                {
                    if ($frequency->getTime() === 'day') {
                        $week_start->setISODate($year, $week_no);

                        for ($i = 0; $i < 7; $i++) {

                            for ($i = 0; $i < $frequency->getNbRepPerTime(); $i++) {
                                $result[] = $week_start->format('Y-m-d');
                            }

                            $week_start->modify('+1 day');
                        }
                    }

                    if ($frequency->getTime() === 'week') {


                        if ($frequency->getNbRepPerTime() == 1)
                            if ($frequency->getNbRepetition() == 1) {
                                $week_start->setISODate($year, $week_no, 3);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 2) {
                                $week_start->setISODate($year, $week_no, 2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 4);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 3) {
                                $week_start->setISODate($year, $week_no, 1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 3);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 5);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 4) {
                                $week_start->setISODate($year, $week_no, 1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 4);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 6);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 5) {
                                $week_start->setISODate($year, $week_no, 1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 3);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 4);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 5);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($year, $week_no, 7);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }

                }

            }

        }


        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 2) {
            $rangeActualYear = range($first_week_no, $last_week_of_year_no, 2);

            foreach ($rangeActualYear as $week_no) {
                $week_start->setISODate($year, $week_no, 3);
                $result[] = $week_start->format('Y-m-d');
            }

            $rangeNextYear = range(1, $first_week_no, 2);

            if (($first_week_no - 1) != 0) {
                $year = $year + 1;

                foreach ($rangeNextYear as $week_no) {
                    $week_start->setISODate($year, $week_no, 3);
                    $result[] = $week_start->format('Y-m-d');
                }
            }

        }

        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 3) {

            $rangeActualYear = range($first_week_no, $last_week_of_year_no, 3);

            foreach ($rangeActualYear as $week_no) {
                if ($frequency->getNbRepetition() == 1) {
                    $week_start->setISODate($year, $week_no, 3);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($frequency->getNbRepetition() == 2) {
                    $week_start->setISODate($year, $week_no, 4);
                    $result[] = $week_start->format('Y-m-d');
                    $week_start->setISODate($year, $week_no + 3, 1);
                    $result[] = $week_start->format('Y-m-d');
                }

            }

        $rangeNextYear = range(1, $first_week_no, 3);

        if (($first_week_no - 1) != 0) {
            $year = $year + 1;

            foreach ($rangeNextYear as $week_no) {
                if ($frequency->getNbRepetition() == 1) {
                    $week_start->setISODate($year, $week_no, 3);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($frequency->getNbRepetition() == 2) {
                    $week_start->setISODate($year, $week_no, 4);
                    $result[] = $week_start->format('Y-m-d');
                    $week_start->setISODate($year, $week_no + 3, 1);
                    $result[] = $week_start->format('Y-m-d');
                }

            }
        }

        if ($frequency->getTime() === 'month' && $frequency->getNbRepPerTime() == 1) {

            $week_start->setISODate($year, $week_no, 3);
            $result[] = $week_start->format('Y-m-d');
            
        }

    }





    return $result;

    }


}
