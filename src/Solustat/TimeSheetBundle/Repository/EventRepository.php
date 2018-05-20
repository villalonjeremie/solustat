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

        $s = microtime(true);

        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());

        $e = microtime(true);
        $timeChrono = $s - $e;

        $arraySize = count($arrayEvents);

        echo "Memory usage before: " . (memory_get_usage() / 1024) . " KB" . PHP_EOL;
        $s = microtime(true);
        $batchSize = 10;

        for ($i=0; $i < $arraySize; $i++) {
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
    }

    private function getArrayEvents(\DateTime $startingDate, \Solustat\TimeSheetBundle\Entity\Frequency $frequency)
    {
        $result = [];
        $first_week_form = (int) $startingDate->format('W');
        $first_day_form = (int) $startingDate->format('d');
        $first_year_form = (int) $startingDate->format('Y');
        $last_week_of_year = (int) date('W', strtotime( $first_year_form . '-12-31'));
        $dayOfWeekStartingDate = (int)date("w", strtotime($startingDate->format('Y-m-d')));


        $flag_week_december = false;

        if ($last_week_of_year == 1) {
            $last_week_of_year = (int) date('W', strtotime($first_year_form . '-12-24'));
            $lastDayOfLastWeekofTheYear = new \DateTime();
            $lastDayOfLastWeekofTheYear->setISODate($first_year_form,$last_week_of_year)->modify('+6 day');
            if (strtotime($first_year_form . '-12-'.$lastDayOfLastWeekofTheYear->format('d')) <= $startingDate->getTimestamp() &&  $startingDate->getTimestamp() <= strtotime($first_year_form . '-12-31')) {
                $flag_week_december = true;
            }
        }

        $week_start = new \DateTime();

        if ($frequency->getTime() === 'day' || ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 1)) {

            if (!$flag_week_december) {
                $rangeActualYear = range($first_week_form, $last_week_of_year);

                foreach ($rangeActualYear as $key => $week_no) {
                    $week_start->setISODate($first_year_form, $week_no);

                    if ($frequency->getTime() === 'day' && $week_no == $first_week_form) {

                        $week_start->modify('+' . ($dayOfWeekStartingDate - 1) . ' day');

                        for ($i = 0; $i < (7 - $dayOfWeekStartingDate); $i++) {

                            for ($j = 0; $j < $frequency->getNbRepetition(); $j++) {
                                $result[] = $week_start->format('Y-m-d');
                            }

                            $week_start->modify('+1 day');
                        }
                    }

                    if ($frequency->getTime() === 'day' && $week_no != $first_week_form) {
                        for ($i = 0; $i < 7; $i++) {

                            for ($j = 0; $j < $frequency->getNbRepetition(); $j++) {
                                $result[] = $week_start->format('Y-m-d');
                            }

                            $week_start->modify('+1 day');
                        }
                    }

                    if ($frequency->getTime() === 'week') {
                        if ($frequency->getNbRepPerTime() == 1) {
                            if ($frequency->getNbRepetition() == 1) {
                                $day1 = 3;
                                $day2 = 4;

                                if ($key == 0) {
                                    if ($dayOfWeekStartingDate <= 3) {
                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 4) {
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                } else {
                                    $week_start->setISODate($first_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                }
                            }

                            if ($frequency->getNbRepetition() == 2) {
                                $day1 = 2;
                                $day2 = 4;

                                if ($key == 0) {
                                    if ($dayOfWeekStartingDate <= 2) {
                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 3) {
                                        $day1 = 3;
                                        $day2 = 5;
                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 4) {
                                        $day = 4;
                                        $week_start->setISODate($first_year_form, $week_no, $day);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 5) {
                                        $day = 5;
                                        $week_start->setISODate($first_year_form, $week_no, $day);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                } else {
                                    $week_start->setISODate($first_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($first_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                }
                            }

                            if ($frequency->getNbRepetition() == 3) {
                                $day1 = 1;
                                $day2 = 3;
                                $day3 = 5;

                                if ($key == 0) {
                                    if ($dayOfWeekStartingDate == 1) {
                                        $day1 = 1;
                                        $day2 = 3;
                                        $day3 = 5;

                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 2 || $dayOfWeekStartingDate == 3) {
                                        $day2 = 3;
                                        $day3 = 5;
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 4 || $dayOfWeekStartingDate == 5) {
                                        $day3 = 5;
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                } else {
                                    $week_start->setISODate($first_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($first_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($first_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }
                            }

                            if ($frequency->getNbRepetition() == 4) {
                                $day1 = 1;
                                $day2 = 2;
                                $day3 = 4;
                                $day4 = 5;

                                if ($key == 0) {
                                    if ($dayOfWeekStartingDate == 1) {
                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 2) {
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 3 || $dayOfWeekStartingDate == 4) {
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 5 || $dayOfWeekStartingDate == 6) {
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                } else {
                                    $week_start->setISODate($first_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($first_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($first_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($first_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                }
                            }

                            if ($frequency->getNbRepetition() == 5) {
                                $day1 = 1;
                                $day2 = 2;
                                $day3 = 3;
                                $day4 = 4;
                                $day5 = 5;

                                if ($key == 0) {
                                    if ($dayOfWeekStartingDate == 1) {
                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day5);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 2 || $dayOfWeekStartingDate == 3) {
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day5);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 4) {
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day5);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 5) {
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day5);
                                        $result[] = $week_start->format('Y-m-d');
                                    }

                                    if ($dayOfWeekStartingDate == 6 || $dayOfWeekStartingDate == 7) {
                                        $week_start->setISODate($first_year_form, $week_no, $day5);
                                        $result[] = $week_start->format('Y-m-d');
                                    } else {
                                        $week_start->setISODate($first_year_form, $week_no, $day1);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day2);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day3);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day4);
                                        $result[] = $week_start->format('Y-m-d');
                                        $week_start->setISODate($first_year_form, $week_no, $day5);
                                        $result[] = $week_start->format('Y-m-d');
                                    }
                                }
                            }
                        }
                    }
                }
            }


            //-----------------------------------------------------------------------------------//

            $next_year_form = $first_year_form + 1;

            if ($flag_week_december) {
                $first_week_form = (int)date('W', strtotime($next_year_form . '-12-24'));
            }

            $rangeNextYear = range(1, $first_week_form);

            foreach ($rangeNextYear as $key => $week_no) {

                if ($frequency->getTime() === 'day') {

                    if ($key == 0) {

                        $week_start->setISODate($next_year_form, $week_no);
                        $week_start->modify('+' . ($dayOfWeekStartingDate - 1) . ' day');

                        for ($i = 0; $i < (7 - $dayOfWeekStartingDate); $i++) {

                            for ($j = 0; $j < $frequency->getNbRepetition(); $j++) {
                                $result[] = $week_start->format('Y-m-d');
                            }

                            $week_start->modify('+1 day');
                        }
                    }

                    if ($key != 1) {
                        for ($i = 0; $i < 7; $i++) {

                            for ($j = 0; $j < $frequency->getNbRepetition(); $j++) {
                                $result[] = $week_start->format('Y-m-d');
                            }

                            $week_start->modify('+1 day');
                        }
                    }
                }

                if ($frequency->getTime() === 'week') {

                    if ($frequency->getNbRepPerTime() == 1) {
                        if ($frequency->getNbRepetition() == 1) {
                            $day1 = 3;
                            $day2 = 4;

                            if ($key == 0) {
                                if ($dayOfWeekStartingDate <= 3) {
                                    $week_start->setISODate($next_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 4) {
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                            } else {
                                $week_start->setISODate($next_year_form, $week_no, $day1);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }

                        if ($frequency->getNbRepetition() == 2) {
                            $day1 = 2;
                            $day2 = 3;
                            $day3 = 4;
                            $day4 = 5;

                            if ($key == 0) {
                                if ($dayOfWeekStartingDate <= 2) {
                                    $week_start->setISODate($next_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 3) {
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 4) {
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 5) {
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                            } else {
                                $week_start->setISODate($next_year_form, $week_no, $day1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day3);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }

                        if ($frequency->getNbRepetition() == 3) {
                            $day1 = 1;
                            $day2 = 3;
                            $day3 = 5;

                            if ($key == 0) {
                                if ($dayOfWeekStartingDate == 1) {
                                    $week_start->setISODate($next_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 2 || $dayOfWeekStartingDate == 3) {
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 4 || $dayOfWeekStartingDate == 5) {
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                            } else {
                                $week_start->setISODate($next_year_form, $week_no, $day1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day3);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }

                        if ($frequency->getNbRepetition() == 4) {
                            $day1 = 1;
                            $day2 = 2;
                            $day3 = 4;
                            $day4 = 5;

                            if ($key == 0) {
                                if ($dayOfWeekStartingDate == 1) {
                                    $week_start->setISODate($next_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 2) {
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 3 || $dayOfWeekStartingDate == 4) {
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 5) {
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                }
                            } else {
                                $week_start->setISODate($next_year_form, $week_no, $day1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day3);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day4);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }

                        if ($frequency->getNbRepetition() == 5) {
                            $day1 = 1;
                            $day2 = 2;
                            $day3 = 3;
                            $day4 = 4;
                            $day5 = 5;

                            if ($key == 0) {
                                if ($dayOfWeekStartingDate == 1) {
                                    $week_start->setISODate($next_year_form, $week_no, $day1);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day5);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 2 || $dayOfWeekStartingDate == 3) {
                                    $week_start->setISODate($next_year_form, $week_no, $day2);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day5);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 4) {
                                    $week_start->setISODate($next_year_form, $week_no, $day3);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day5);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                                if ($dayOfWeekStartingDate == 5) {
                                    $week_start->setISODate($next_year_form, $week_no, $day4);
                                    $result[] = $week_start->format('Y-m-d');
                                    $week_start->setISODate($next_year_form, $week_no, $day5);
                                    $result[] = $week_start->format('Y-m-d');
                                }

                            } else {
                                $week_start->setISODate($next_year_form, $week_no, $day1);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day2);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day3);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day4);
                                $result[] = $week_start->format('Y-m-d');
                                $week_start->setISODate($next_year_form, $week_no, $day5);
                                $result[] = $week_start->format('Y-m-d');
                            }
                        }
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
                $next_year_form = $first_year_form + 1;

                foreach ($rangeNextYear as $week_no) {
                    if ($frequency->getNbRepetition() == 1) {
                        $week_start->setISODate($next_year_form, $week_no, 3);
                        $result[] = $week_start->format('Y-m-d');
                    }

                    if ($frequency->getNbRepetition() == 2) {
                        $week_start->setISODate($next_year_form, $week_no, 4);
                        $result[] = $week_start->format('Y-m-d');
                        $week_start->setISODate($next_year_form, $week_no + 3, 1);
                        $result[] = $week_start->format('Y-m-d');
                    }
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

        return $result;
    }
}
