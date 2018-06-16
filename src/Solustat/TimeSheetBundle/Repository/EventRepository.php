<?php
namespace Solustat\TimeSheetBundle\Repository;

use Solustat\TimeSheetBundle\Entity\Event;
use Solustat\TimeSheetBundle\Entity\User;
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
    const CARE_1H_TIMESTAMP = 3600;
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

    public function insertNewBulkEvents(array $entities)
    {
        $patient = $entities['patient'];

        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());

        $arraySize = count($arrayEvents);

        $date = new \Datetime('now');

        for ($i=0; $i < $arraySize; $i++) {
            $event = new Event();
            $event->setTitle($patient->getName().' '.$patient->getSurname());
            $event->setVisitDate(new \Datetime($arrayEvents[$i]));
            $event->setCreatedAt($date);
            $event->setPatient($entities['patient']);
            $event->setUser($entities['user']);
            $event->setVisitTime($entities['visit_time']);
            $event->setLinked(1);
            $event->setAutoGenerate(1);
            $this->_em->persist($event);
        }

        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (ORMException $e) {
            throw new NoSuchMetadataException($e);
        }
    }

    public function insertUpdateBulkEvents(array $entities)
    {
        $patient = $entities['patient'];

        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());

        $arraySize = count($arrayEvents);

        $date = new \Datetime('now');

        for ($i=0; $i < $arraySize; $i++) {
            $event = new Event();
            $event->setTitle($patient->getName().' '.$patient->getSurname());
            $event->setVisitDate(new \Datetime($arrayEvents[$i]));
            $event->setCreatedAt($date);
            $event->setPatient($entities['patient']);
            $event->setUser($entities['user']);
            $event->setVisitTime($entities['visit_time']);
            $event->setVisitTime($entities['visit_time']);

            $this->_em->persist($event);
        }

        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (ORMException $e) {
            throw new NoSuchMetadataException($e);
        }
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
    private function setResultPerYear($freq)
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
    protected function setResultPerMonth($freq)
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
    protected function setResultOnePerWeek($freq)
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
    protected function setResultPerDay($freq)
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

    public function insertEvent(User $user, \DateTime $startingDate, \DateTime $endDate, Array $filter){

        $visitTimeStamp = $endDate->getTimestamp()-$startingDate->getTimestamp();

        if($visitTimeStamp >= self::CARE_1H_TIMESTAMP) {
            $visitTime = $this->_em->getRepository('SolustatTimeSheetBundle:VisitTime')->findByName('Soins durée 1h');
        } else {
            $visitTime = $this->_em->getRepository('SolustatTimeSheetBundle:VisitTime')->findByName('Soins normaux');
        }


        $event = new Event();
        $event->setTitle($filter['title']);
        $event->setVisitDate($startingDate);
        $event->setCreatedAt(new \DateTime('now', new \DateTimeZone('America/Montreal')));
        $event->setPatient($filter['patient']);
        $event->setUser($filter['userCurrent']);
        $event->setVisitTime($visitTime);
        $this->_em->persist($event);
    }

    public function updateEvent(){

    }

    public function deleteEvent(){

    }

}
