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
        $first_week_form = (int) $startingDate->format('W');
        $first_day_form = (int) $startingDate->format('d');
        $first_year_form = (int) $startingDate->format('Y');

        $last_week_of_year = (int) date('W', strtotime( $first_year_form . '-12-31'));

        if ($last_week_of_year == 1) {
            $last_week_of_year = (int) date('W', strtotime($first_year_form . '-12-24'));
        }

        $week_start = new \DateTime();

        if ($frequency->getTime() === 'day' || ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 1)) {

            $rangeActualYear = range($first_week_form, $last_week_of_year);

            foreach ($rangeActualYear as $week_no)
            {
                $week_start->setISODate($first_year_form, $week_no);

                if ($frequency->getTime() === 'day' && $week_no == $first_week_form) {

                    $dayOfWeek = (int)date("w", strtotime($startingDate->format('Y-m-d')));
                    $week_start->modify('+'.($dayOfWeek-1).' day');

                    for ($i = 0; $i < (7 - $dayOfWeek); $i++) {

                        for ($i = 0; $i < $frequency->getNbRepPerTime(); $i++) {
                            $result[] = $week_start->format('Y-m-d');
                        }

                        $week_start->modify('+1 day');
                    }
                } else {
                    for ($i = 0; $i < 7; $i++) {

                        for ($i = 0; $i < $frequency->getNbRepPerTime(); $i++) {
                            $result[] = $week_start->format('Y-m-d');
                        }

                        $week_start->modify('+1 day');
                    }
                }

                if ($frequency->getTime() === 'week') {

                    if ($frequency->getNbRepetition() == 1) {
                        $week_start->setISODate($first_year_form, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 2) {
                        $week_start->setISODate($first_year_form, $week_no, 2);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 3) {
                        $week_start->setISODate($first_year_form, $week_no, 1);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 5);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 4) {
                        $week_start->setISODate($first_year_form, $week_no, 1);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 2);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 6);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 5) {
                        $week_start->setISODate($first_year_form, $week_no, 1);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 5);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($first_year_form, $week_no, 7);
                        $result[] = $week_start->format('Y-m-d');
                    }

                }

            }

            $rangeNextYear = range(1, $first_week_form);

                $first_year_form = $first_year_form + 1;

                foreach ($rangeNextYear as $week_no)
                {
                    if ($frequency->getTime() === 'day' && $week_no == $first_week_form) {
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
                                $week_start->setISODate($first_year_form, $week_no, 3);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 2) {
                                $week_start->setISODate($first_year_form, $week_no, 2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 4);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 3) {
                                $week_start->setISODate($first_year_form, $week_no, 1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 3);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 5);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 4) {
                                $week_start->setISODate($first_year_form, $week_no, 1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 4);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 6);
                                $result[] = $week_start->format('Y-m-d');
                            }

                            if ($frequency->getNbRepetition() == 5) {
                                $week_start->setISODate($first_year_form, $week_no, 1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 3);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 4);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 5);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($first_year_form, $week_no, 7);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }
                }

        }

        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 2) {
            $rangeActualYear = range($first_week_form, $last_week_of_year, 2);

            foreach ($rangeActualYear as $week_no) {
                $week_start->setISODate($first_year_form, $week_no, 3);
                $result[] = $week_start->format('Y-m-d');
            }

            $rangeNextYear = range(1, $first_week_form, 2);

            if (($first_week_form - 1) != 0) {
                $first_year_form = $first_year_form + 1;

                foreach ($rangeNextYear as $week_no) {
                    $week_start->setISODate($first_year_form, $week_no, 3);
                    $result[] = $week_start->format('Y-m-d');
                }
            }

        }

        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 3) {

            $rangeActualYear = range($first_week_form, $last_week_of_year, 3);

            foreach ($rangeActualYear as $week_no) {
                if ($frequency->getNbRepetition() == 1) {
                    $week_start->setISODate($first_year_form, $week_no, 3);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($frequency->getNbRepetition() == 2) {
                    $week_start->setISODate($first_year_form, $week_no, 4);
                    $result[] = $week_start->format('Y-m-d');
                    $week_start->setISODate($first_year_form, $week_no + 3, 1);
                    $result[] = $week_start->format('Y-m-d');
                }

            }

        $rangeNextYear = range(1, $first_week_form, 3);

        if (($first_week_form - 1) != 0) {
            $first_year_form = $first_year_form + 1;

            foreach ($rangeNextYear as $week_no) {
                if ($frequency->getNbRepetition() == 1) {
                    $week_start->setISODate($first_year_form, $week_no, 3);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($frequency->getNbRepetition() == 2) {
                    $week_start->setISODate($first_year_form, $week_no, 4);
                    $result[] = $week_start->format('Y-m-d');
                    $week_start->setISODate($first_year_form, $week_no + 3, 1);
                    $result[] = $week_start->format('Y-m-d');
                }
            }
        }

        if ($frequency->getTime() === 'month' && $frequency->getNbRepPerTime() == 1) {

            $rangeActualYear = range($first_week_form, $last_week_of_year, 4);

            foreach ($rangeActualYear as $week_no) {
                    $week_start->setISODate($first_year_form, $week_no, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
            }

            $rangeNextYear = range(1, $first_week_form, 4);

            foreach ($rangeNextYear as $week_no) {
                $week_start->setISODate($first_year_form, $week_no, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

        }

        if ($frequency->getTime() === 'month' && $frequency->getNbRepPerTime() == 2) {

            $rangeActualYear = range($first_week_form, $last_week_of_year, 4);

            foreach ($rangeActualYear as $week_no) {
                $week_start->setISODate($first_year_form, $week_no, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
                $week_start->setISODate($first_year_form, $week_no + 2, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            $rangeNextYear = range(1, $first_week_form, 4);

            foreach ($rangeNextYear as $week_no) {
                $week_start->setISODate($first_year_form, $week_no, 3);
                $result[] = $week_start->format('Y-m-d');
                $week_start->setISODate($first_year_form, $week_no + 2, 4);
                $result[] = $week_start->format('Y-m-d');
            }
        }

        if ($frequency->getTime() === 'month' && $frequency->getNbRepPerTime() == 3) {

            $rangeActualYear = range($first_week_form, $last_week_of_year, 4);

            foreach ($rangeActualYear as $week_no) {
                $week_start->setISODate($first_year_form, $week_no, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
                $week_start->setISODate($first_year_form, $week_no + 2, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
                $week_start->setISODate($first_year_form, $week_no + 3, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            $rangeNextYear = range(1, $first_week_form, 4);

            foreach ($rangeNextYear as $week_no) {
                $week_start->setISODate($first_year_form, $week_no, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
                $week_start->setISODate($first_year_form, $week_no + 2, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
                $week_start->setISODate($first_year_form, $week_no + 3, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

        }

        if ($frequency->getTime() === 'year' && $frequency->getNbRepPerTime() == 1) {
            $week_start->setISODate($first_year_form, $first_week_form, $first_day_form);
            $result[] = $week_start->format('Y-m-d');
        }

        if ($frequency->getTime() === 'year' && $frequency->getNbRepPerTime() == 2) {
            $week_start->setISODate($first_year_form, $first_week_form, $first_day_form);
            $result[] = $week_start->format('Y-m-d');

            if ($first_week_form + 26 > $last_week_of_year) {
                $week = $first_week_form + 26 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 26;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }
        }

        if ($frequency->getTime() === 'year' && $frequency->getNbRepPerTime() == 3) {
            $week_start->setISODate($first_year_form, $first_week_form, $first_day_form);
            $result[] = $week_start->format('Y-m-d');

            if ($first_week_form + 17 > $last_week_of_year) {
                $week = $first_week_form + 17 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 17;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            if ($first_week_form + 34 > $last_week_of_year) {
                $week = $first_week_form + 34 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 34;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }
        }

        if ($frequency->getTime() === 'year' && $frequency->getNbRepPerTime() == 4) {
            $week_start->setISODate($first_year_form, $first_week_form, $first_day_form);
            $result[] = $week_start->format('Y-m-d');

            if ($first_week_form + 13 > $last_week_of_year) {
                $week = $first_week_form + 13 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 13;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            if ($first_week_form + 26 > $last_week_of_year) {
                $week = $first_week_form + 26 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 26;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            if ($first_week_form + 39 > $last_week_of_year) {
                $week = $first_week_form + 39 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 39;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }
        }

        if ($frequency->getTime() === 'year' && $frequency->getNbRepPerTime() == 5) {
            $week_start->setISODate($first_year_form, $first_week_form, $first_day_form);
            $result[] = $week_start->format('Y-m-d');

            if ($first_week_form + 10 > $last_week_of_year) {
                $week = $first_week_form + 10 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 10;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            if ($first_week_form + 20 > $last_week_of_year) {
                $week = $first_week_form + 20 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 20;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            if ($first_week_form + 30 > $last_week_of_year) {
                $week = $first_week_form + 30 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 30;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }

            if ($first_week_form + 40 > $last_week_of_year) {
                $week = $first_week_form + 40 - $last_week_of_year;
                $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            } else {
                $second_week = $first_week_form + 40;
                $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                $result[] = $week_start->format('Y-m-d');
            }
        }

            if ($frequency->getTime() === 'year' && $frequency->getNbRepPerTime() == 6) {
                $week_start->setISODate($first_year_form, $first_week_form, $first_day_form);
                $result[] = $week_start->format('Y-m-d');

                if ($first_week_form + 8 > $last_week_of_year) {
                    $week = $first_week_form + 8 - $last_week_of_year;
                    $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                } else {
                    $second_week = $first_week_form + 8;
                    $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($first_week_form + 16 > $last_week_of_year) {
                    $week = $first_week_form + 16 - $last_week_of_year;
                    $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                } else {
                    $second_week = $first_week_form + 16;
                    $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($first_week_form + 24 > $last_week_of_year) {
                    $week = $first_week_form + 24 - $last_week_of_year;
                    $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                } else {
                    $second_week = $first_week_form + 24;
                    $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($first_week_form + 32 > $last_week_of_year) {
                    $week = $first_week_form + 32 - $last_week_of_year;
                    $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                } else {
                    $second_week = $first_week_form + 32;
                    $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                }

                if ($first_week_form + 40 > $last_week_of_year) {
                    $week = $first_week_form + 40 - $last_week_of_year;
                    $week_start->setISODate($first_year_form + 1, $week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                } else {
                    $second_week = $first_week_form + 40;
                    $week_start->setISODate($first_year_form, $second_week, $first_day_form);
                    $result[] = $week_start->format('Y-m-d');
                }
            }

    }

    return $result;

    }


}
