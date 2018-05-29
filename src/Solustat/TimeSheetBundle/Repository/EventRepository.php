<?php
namespace Solustat\TimeSheetBundle\Repository;

use Solustat\TimeSheetBundle\Entity\Event;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityRepository;

/**
 * EventRepository
 *
 */
class EventRepository extends EntityRepository
{
    const ONE_TIME_PER_2_WEEK_DAY_1 = 3;
    const ONE_TIME_PER_3_WEEK_DAY_1 = 3;
    const TWO_TIME_PER_3_WEEK_DAY_1 = 4;
    const TWO_TIME_PER_3_WEEK_DAY_2 = 3;
    const INTERVAL_TIME_PLANNING_WEEKS = 52;

    protected $setFreqYear = [
        1 => [0],
        2 => [0, 26],
        3 => [0, 17, 34],
        4 => [0, 13, 26, 39],
        5 => [0, 10, 20, 30, 40],
        6 => [0, 8, 16, 24, 32, 40]
    ];

    protected $setFreqMonth = [
        1 => [0],
        2 => [0, 2],
        3 => [0, 1, 3]
    ];

    protected $setFreqWeekPerDay = [
        1 => [3],
        2 => [2, 4],
        3 => [1, 3, 5],
        4 => [1, 2, 4, 5],
        5 => [1, 2, 3, 4, 5]
    ];

    protected $firstWeekForm;
    protected $firstYearForm;
    protected $lastWeekOfYear;
    protected $dayOfWeekStartingDate;
    protected $lastDayOfLastWeekOfTheYear;
    protected $weekStart;

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
        $this->firstWeekForm = (int) $startingDate->format('W');
        $this->firstYearForm = (int) $startingDate->format('Y');
        $this->lastWeekOfYear = (int) date('W', strtotime( $this->firstYearForm . '-12-31'));
        $this->dayOfWeekStartingDate = (int)date("w", strtotime($startingDate->format('Y-m-d')));

        if ($this->lastWeekOfYear == 1) {
            $this->lastWeekOfYear = (int) date('W', strtotime($this->firstYearForm . '-12-24'));
            $this->lastDayOfLastWeekOfTheYear = new \DateTime();
            $this->lastDayOfLastWeekOfTheYear->setISODate($this->firstYearForm, $this->lastWeekOfYear)->modify('+6 day');
            if (strtotime($this->firstYearForm . '-12-'.($this->lastDayOfLastWeekOfTheYear->format('d')+1)) <= $startingDate->getTimestamp() &&
                $startingDate->getTimestamp() <= strtotime($this->firstYearForm . '-12-31')
            ) {
                $this->firstWeekForm = $this->lastWeekOfYear + 1;
            }
        }

        $this->weekStart = new \DateTime();

        if ($frequency->getTime() === 'day') {
            $result = $this->setResultPerDay($frequency->getNbRepetition());
        }

        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 1) {
            $result = $this->setResultOnePerWeek($frequency->getNbRepetition());
        }

        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 2) {
            $this->shiftStartDate();
            $rangeWeek = range($this->firstWeekForm, $this->firstWeekForm + self::INTERVAL_TIME_PLANNING_WEEKS, 2);

            foreach ($rangeWeek as $week_no) {
                $this->weekStart->setISODate($this->firstYearForm, $week_no, self::ONE_TIME_PER_2_WEEK_DAY_1);
                $result[] = $this->weekStart->format('Y-m-d');
            }
        }

        if ($frequency->getTime() === 'week' && $frequency->getNbRepPerTime() == 3) {
            $this->shiftStartDate();
            $rangeWeek = range($this->firstWeekForm, $this->firstWeekForm + self::INTERVAL_TIME_PLANNING_WEEKS, 3);

            foreach ($rangeWeek as $week_no) {
                if ($frequency->getNbRepetition() == 1) {
                    $this->weekStart->setISODate($this->firstYearForm, $week_no, self::ONE_TIME_PER_3_WEEK_DAY_1);
                    $result[] = $this->weekStart->format('Y-m-d');
                }

                if ($frequency->getNbRepetition() == 2) {
                    $this->weekStart->setISODate($this->firstYearForm, $week_no, self::TWO_TIME_PER_3_WEEK_DAY_1);
                    $result[] = $this->weekStart->format('Y-m-d');
                    $this->weekStart->setISODate($this->firstYearForm, $week_no + 2, self::TWO_TIME_PER_3_WEEK_DAY_2);
                    $result[] = $this->weekStart->format('Y-m-d');
                }
            }
        }

        if ($frequency->getTime() === 'month') {
            $result = $this->setResultperMonth($frequency->getNbRepetition());
        }

        if ($frequency->getTime() === 'year') {
            $result = $this->setResultperYear($frequency->getNbRepetition());
        }

        return $this->deleteDates($result, $startingDate);
    }

    /**
     * @param array $arrayDates
     * @param \DateTime $startingDate
     * @return array
     */
    private function deleteDates(array $arrayDates, \DateTime $startingDate)
    {
        $dateConfirm = false;

        do {
            if (new \DateTime($arrayDates[0]) < $startingDate){
                array_shift($arrayDates);
            } else {
                $dateConfirm = true;
            }

        } while (!$dateConfirm);
        return $arrayDates;
    }

    /**
     * @param int $freq
     * @return array
     */
    private function setResultPerYear(int $freq) : array
    {
        $this->shiftStartDate();

        foreach ($this->setFreqYear[$freq] as $value) {
            $this->weekStart->setISODate($this->firstYearForm, $this->firstWeekForm + $value, $this->dayOfWeekStartingDate);
            $result[] = $this->weekStart->format('Y-m-d');
        }

        return $result;
    }

    /**
     * @param int $freq
     * @return array
     */
    protected function setResultPerMonth(int $freq) : array
    {
        $this->shiftStartDate();
        $rangeWeek = range($this->firstWeekForm, $this->firstWeekForm + self::INTERVAL_TIME_PLANNING_WEEKS, 4);

        foreach ($rangeWeek as $week_no) {
            foreach ($this->setFreqMonth[$freq] as $value){
                $this->weekStart->setISODate($this->firstYearForm, $week_no + $value, $this->dayOfWeekStartingDate);
                $result[] = $this->weekStart->format('Y-m-d');
            }
        }

        return $result;
    }

    /**
     * @param int $freq
     * @return array
     */
    protected function setResultOnePerWeek(int $freq) : array
    {
        $this->shiftStartDate();
        $rangeWeek = range($this->firstWeekForm, $this->firstWeekForm + self::INTERVAL_TIME_PLANNING_WEEKS);

        foreach ($rangeWeek as $key => $week_no) {
            foreach ($this->setFreqWeekPerDay[$freq] as $value) {
                $this->weekStart->setISODate($this->firstYearForm, $week_no, $value);
                $result[] = $this->weekStart->format('Y-m-d');
            }
        }

        return $result;
    }

    /**
     * @param int $freq
     * @return array
     */
    protected function setResultPerDay(int $freq) : array
    {
        $this->shiftStartDate();
        $rangeWeek = range($this->firstWeekForm, $this->firstWeekForm + self::INTERVAL_TIME_PLANNING_WEEKS);

        foreach ($rangeWeek as $key => $week_no) {
            $this->weekStart->setISODate($this->firstYearForm, $week_no);

            for ($i = 0; $i < 7; $i++) {
                for ($j = 0; $j < $freq; $j++) {
                    $result[] = $this->weekStart->format('Y-m-d');
                }
                $this->weekStart->modify('+1 day');
            }
        }

        return $result;
    }

    protected function shiftStartDate()
    {
        if ($this->dayOfWeekStartingDate == 0 || $this->dayOfWeekStartingDate == 6) {
            $this->firstWeekForm++;
            $this->dayOfWeekStartingDate = 1;
        }
    }
}
