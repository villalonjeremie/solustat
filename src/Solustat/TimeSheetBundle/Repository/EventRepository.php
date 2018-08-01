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

    const MAXIMUM_VISIT_PER_DAY = 1;
    const WEEKEND_OFF_SHIFTING_VISIT = true;
    const IA_AMPLITUDE_NEGATIVE = true;

    const AMPLITUDE_MONTH = 5;
    const AMPLITUDE_YEAR = 5;

    const AMPLITUDE_WEEK_PER_DAY_1 = 4;
    const AMPLITUDE_WEEK_PER_DAY_2 = 1;
    const AMPLITUDE_WEEK_PER_DAY_3 = 1;
    const AMPLITUDE_WEEK_PER_DAY_4 = 0;
    const AMPLITUDE_WEEK_PER_DAY_5 = 0;

    const AMPLITUDE_ONE_TIME_PER_2_WEEK_DAY = 2;
    const AMPLITUDE_ONE_TIME_PER_3_WEEK_DAY = 3;
    const AMPLITUDE_TWO_TIME_PER_3_WEEK_DAY = 2;


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

    const HOUR_START_DAY = 8;

    /**
     * @var
     */
    protected $firstWeekForm;

    /**
     * @var
     */
    protected $firstYearForm;

    /**
     * @var
     */
    protected $lastWeekOfYear;

    /**
     * @var
     */
    protected $dayOfWeekStartingDate;

    /**
     * @var
     */
    protected $lastDayOfLastWeekOfTheYear;

    /**
     * @var
     */
    protected $weekStart;

    /**
     * @var
     */
    protected $user;

    /**
     * @param User $user
     * @param array $entities
     */
    public function insertNewBulkEvents(User $user, array $entities)
    {
        $patient = $entities['patient'];
        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());

        /****************************************************************/
        $numberVisitSet = $user->getNumberVisitSet();
        if ($numberVisitSet) {
            $numberVisitSet = unserialize($numberVisitSet);
        } else {
            $numberVisitSet = [];
        }
        /****************************************************************/

        $arraySize = count($arrayEvents);
        $date = new \DateTime('now');

        $starttime = microtime(true);

        $startVisitTimeStamp = self::HOUR_START_DAY * 3600;
        $timeStampVisit = $entities['visit_time']->getTimeStamp();

        $intervalToInserted = [];

        for ($i=0; $i < $arraySize; $i++) {

            /****************************************************************/
            $dateShifted = $this->shiftDate($arrayEvents[$i], $numberVisitSet, $patient->getFrequency());

            $numberVisitSet[$dateShifted][] = $patient->getId();
            ksort($numberVisitSet);
            $arrayEvents[$i] = $dateShifted;
            /****************************************************************/

            $arrayFirst = [];
            $result = $this->getEventByTimeUser($user,$arrayEvents[$i]);

            if (empty($result)) {
                $interval = $startVisitTimeStamp.'-'.($startVisitTimeStamp + $timeStampVisit);
                $time = $startVisitTimeStamp;
            } else {
                foreach ($result as $ev) {
                    $arrayFirst[] = $ev['intervalVisitTime'];
                }

                $resultTime = array_merge($arrayFirst, $intervalToInserted);
                sort($resultTime);
                $sizeIntervals = count($resultTime);

                if ($sizeIntervals == 0) {
                    $interval = $startVisitTimeStamp . '-' . ($startVisitTimeStamp + $timeStampVisit);
                    $time = $startVisitTimeStamp;
                }

                if ($sizeIntervals == 1) {
                    list($start, $end) = preg_split("/-/", $resultTime[0]);
                    $interval = ($end) . '-' . ($end + $timeStampVisit);
                    $time = $end;
                }

                if ($sizeIntervals > 1) {
                    $flagDone = false;
                    $j = 0;

                    while ($j < $sizeIntervals - 1) {
                        list($start, $end) = preg_split("/-/", $resultTime[$j]);
                        list($startNext, $endNext) = preg_split("/-/", $resultTime[$j+1]);

                        if ($end != $startNext && (($startNext - $end) >= $timeStampVisit)) {
                            $interval = $end . '-' . ($end + $timeStampVisit);
                            $time = $end;
                            $flagDone = true;
                            break;
                        }
                        $j++;
                    }

                    if (!$flagDone) {
                        $interval = ($endNext) . '-' . ($endNext + $timeStampVisit);
                        $time = $endNext;
                    }
                }
            }

            $datetime = date('Y-m-d H:i:s', strtotime($arrayEvents[$i]) + $time);

            $event = new Event();
            $event->setTitle($patient->getName().' '.$patient->getSurname());
            $event->setVisitDate(new \Datetime($datetime));
            $event->setCreatedAt($date);
            $event->setPatient($entities['patient']);
            $event->setUser($entities['user']);
            $event->setVisitTime($entities['visit_time']);
            $event->setLinked(1);
            $event->setAutoGenerate(1);
            $event->setNurse($entities['user']);
            $event->setDateKey($arrayEvents[$i]);
            $event->setIntervalVisitTime($interval);
            $this->_em->persist($event);

            //if x time per day
            if ($i < $arraySize-1){
                if ($arrayEvents[$i] == $arrayEvents[$i+1]){
                    $intervalToInserted[] = $interval;
                } else {
                    $intervalToInserted = [];
                }
            } else {
                $intervalToInserted = [];
            }
        }

        /****************************************************************/
        $numberVisitSet = serialize($numberVisitSet);
        $user->setNumberVisitSet($numberVisitSet);
        $this->_em->persist($user);
        /****************************************************************/

        try {
            $this->_em->flush();
            $this->_em->clear();
            $endtime = microtime(true);
            $duration = $endtime - $starttime;
        } catch (ORMException $e) {
            throw new NoSuchMetadataException($e);
        }
    }

    /**
     * @param User $user
     * @param array $entities
     */
    public function insertUpdateBulkEvents(User $user, array $entities)
    {
        $patient = $entities['patient'];
        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());

        /****************************************************************/
        $numberVisitSet = $user->getNumberVisitSet();
        $result = $this->deleteVisitTimeBulk($numberVisitSet, $patient->getId());

        if ($result[0]) {
            $numberVisitSet = unserialize($result[0]);
        } else {
            $numberVisitSet = [];
        }
        /****************************************************************/

        $arraySize = count($arrayEvents);
        $date = new \DateTime('now');
        $starttime = microtime(true);

        $startVisitTimeStamp = self::HOUR_START_DAY * 3600;
        $timeStampVisit = $entities['visit_time']->getTimeStamp();

        $intervalToInserted = [];

        for ($i=0; $i < $arraySize; $i++) {

            /****************************************************************/
            $dateShifted = $this->shiftDate($arrayEvents[$i], $numberVisitSet, $patient->getFrequency());

            $numberVisitSet[$dateShifted][] = $patient->getId();
            ksort($numberVisitSet);
            $arrayEvents[$i] = $dateShifted;
            /****************************************************************/

            $arrayFirst = [];
            $result = $this->getEventByTimeUser($user,$arrayEvents[$i]);

            if (empty($result)) {
                $interval = $startVisitTimeStamp.'-'.($startVisitTimeStamp + $timeStampVisit);
                $time = $startVisitTimeStamp;
            } else {
                foreach ($result as $ev) {
                    $arrayFirst[] = $ev['intervalVisitTime'];
                }

                $resultTime = array_merge($arrayFirst, $intervalToInserted);
                sort($resultTime);
                $sizeIntervals = count($resultTime);

                if ($sizeIntervals == 0) {
                    $interval = $startVisitTimeStamp . '-' . ($startVisitTimeStamp + $timeStampVisit);
                    $time = $startVisitTimeStamp;
                }

                if ($sizeIntervals == 1) {
                    list($start, $end) = preg_split("/-/", $resultTime[0]);
                    $interval = ($end) . '-' . ($end + $timeStampVisit);
                    $time = $end;
                }

                if ($sizeIntervals > 1) {
                    $flagDone = false;
                    $j = 0;

                    while ($j < $sizeIntervals - 1) {
                        list($start, $end) = preg_split("/-/", $resultTime[$j]);
                        list($startNext, $endNext) = preg_split("/-/", $resultTime[$j+1]);

                        if ($end != $startNext && (($startNext - $end) >= $timeStampVisit)) {
                            $interval = $end . '-' . ($end + $timeStampVisit);
                            $time = $end;
                            $flagDone = true;
                            break;
                        }
                        $j++;
                    }

                    if (!$flagDone) {
                        $interval = ($endNext) . '-' . ($endNext + $timeStampVisit);
                        $time = $endNext;
                    }
                }
            }

            $datetime = date('Y-m-d H:i:s', strtotime($arrayEvents[$i]) + $time);

            $event = new Event();
            $event->setTitle($patient->getName().' '.$patient->getSurname());
            $event->setVisitDate(new \Datetime($datetime));
            $event->setCreatedAt($date);
            $event->setUpdatedAt($date);
            $event->setPatient($entities['patient']);
            $event->setUser($entities['user']);
            $event->setVisitTime($entities['visit_time']);
            $event->setLinked(1);
            $event->setAutoGenerate(1);
            $event->setNurse($entities['user']);
            $event->setDateKey($arrayEvents[$i]);
            $event->setIntervalVisitTime($interval);
            $this->_em->persist($event);

            //if x time per day
            if($i < $arraySize -1){
                if ($arrayEvents[$i] == $arrayEvents[$i+1]){
                    $intervalToInserted[] = $interval;
                } else {
                    $intervalToInserted = [];
                }
            } else {
                $intervalToInserted = [];
            }
        }

        /****************************************************************/
        $numberVisitSet = serialize($numberVisitSet);
        $user->setNumberVisitSet($numberVisitSet);
        $this->_em->persist($user);
        /****************************************************************/

        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (ORMException $e) {
            throw new NoSuchMetadataException($e);
        }
    }

    /**
     * @param \DateTime $startingDate
     * @param \Solustat\TimeSheetBundle\Entity\Frequency $frequency
     * @return array
     */
    private function getArrayEvents(\DateTime $startingDate, \Solustat\TimeSheetBundle\Entity\Frequency $frequency)
    {
        $result = [];
        $this->firstWeekForm = (int) $startingDate->format('W');
        $this->firstYearForm = (int) $startingDate->format('Y');
        $this->lastWeekOfYear = (int) date('W', strtotime( $this->firstYearForm . '-12-31'));
        $this->dayOfWeekStartingDate = (int)date("w", strtotime($startingDate->format('Y-m-d')));

        if ($this->lastWeekOfYear == 1) {
            $this->lastWeekOfYear = (int) date('W', strtotime($this->firstYearForm . '-12-24'));
            $this->lastDayOfLastWeekOfTheYear = new \DateTime('now');
            $this->lastDayOfLastWeekOfTheYear->setISODate($this->firstYearForm, $this->lastWeekOfYear)->modify('+6 day');
            if (strtotime($this->firstYearForm . '-12-'.($this->lastDayOfLastWeekOfTheYear->format('d')+1)) <= $startingDate->getTimestamp() &&
                $startingDate->getTimestamp() <= strtotime($this->firstYearForm . '-12-31')
            ) {
                $this->firstWeekForm = $this->lastWeekOfYear + 1;
            }
        }

        $this->weekStart = new \DateTime('now');

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
        if ($this->dayOfWeekStartingDate == 0 || $this->dayOfWeekStartingDate == 6)
        {
            $this->firstWeekForm++;
            $this->dayOfWeekStartingDate = 1;
        }
    }

    /**
     * @param User $user
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array $filter
     * @return Event
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function insertEvent(User $user, \DateTime $startDate, \DateTime $endDate, Array $filter)
    {
        $visitTimeStamp = $endDate->getTimestamp()-$startDate->getTimestamp();

        if($visitTimeStamp >= self::CARE_1H_TIMESTAMP) {
            $visitTime = $this->_em->getRepository('SolustatTimeSheetBundle:VisitTime')->findByName('Soins durée 1h');
        } else {
            $visitTime = $this->_em->getRepository('SolustatTimeSheetBundle:VisitTime')->findByName('Soins normaux');
        }

        $patient = $this->_em->getRepository('SolustatTimeSheetBundle:Patient')->find($filter['patientId']);

        /****************************************************************/
        $arrayEvents[0] = $startDate->format('Y-m-d');
        $numberVisitSet = $user->getNumberVisitSet();
        $numberVisitSetUpdated = $this->updateVisitTimeSet($numberVisitSet, $arrayEvents, $patient->getId());
        $user->setNumberVisitSet($numberVisitSetUpdated);
        $this->_em->persist($user);
        /****************************************************************/

        $event = new Event();
        $event->setTitle($patient->getName().' '.$patient->getSurname());
        $event->setVisitDate($startDate);
        $event->setCreatedAt(new \DateTime('now'));
        $event->setPatient($patient);
        $event->setUser($filter['userCurrent']);
        $event->setVisitTime($visitTime[0]);
        $event->setLinked(1);
        $event->setAutoGenerate(0);
        $event->setNurse($filter['userCurrent']);
        $event->setDateKey($startDate->format('Y-m-d'));
        $this->_em->persist($event);
        $this->_em->flush();
        $this->_em->clear();

        return $event;
    }

    /**
     * @param User $user
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array $filter
     * @return null|object
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateEvent(User $user, \DateTime $startDate, \DateTime $endDate, Array $filter)
    {
        $event = $this->_em->getRepository('SolustatTimeSheetBundle:Event')->find($filter['id']);
        $visitTimeStamp = $endDate->getTimestamp() - $startDate->getTimestamp();

        if($visitTimeStamp >= self::CARE_1H_TIMESTAMP) {
            $visitTime = $this->_em->getRepository('SolustatTimeSheetBundle:VisitTime')->findByName('Soins durée 1h');
        } else {
            $visitTime = $this->_em->getRepository('SolustatTimeSheetBundle:VisitTime')->findByName('Soins normaux');
        }

        /****************************************************************/
        $arrayEvents[0] = $startDate->format('Y-m-d');
        $numberVisitSet = $user->getNumberVisitSet();
        $numberVisitSetDeleted = $this->deleteVisitTime($numberVisitSet, $event->getDateKey(), $event->getPatient()->getId());
        $numberVisitSetUpdated = $this->updateVisitTimeSet($numberVisitSetDeleted, $arrayEvents, $event->getPatient()->getId());
        $user->setNumberVisitSet($numberVisitSetUpdated);
        $this->_em->persist($user);
        /****************************************************************/

        $event->setVisitDate($startDate);
        $event->setUpdatedAt(new \DateTime('now'));
        $event->setVisitTime($visitTime[0]);
        $event->setAutoGenerate(0);
        $event->setLinked(1);
        $event->setDateKey($startDate->format('Y-m-d'));
        $this->_em->persist($event);
        $this->_em->flush();
        $this->_em->clear();

        return $event;
    }

    /**
     * @param $filters
     * @return int
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteEvent($filters)
    {
        $event = $this->_em->getRepository('SolustatTimeSheetBundle:Event')->find($filters['id']);
        $user = $filters['userCurrent'];
        /****************************************************************/
        $numberVisitSet = $user->getNumberVisitSet();
        $numberVisitSetDeleted = $this->deleteVisitTime($numberVisitSet, $event->getDateKey(), $event->getPatient()->getId());
        $user->setNumberVisitSet($numberVisitSetDeleted);
        $this->_em->persist($user);
        /****************************************************************/

        $this->_em->remove($event);
        $this->_em->flush();
        $this->_em->clear();

        return 1;
    }

    /**
     * @param $id
     * @return int
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unlinkEvent($id)
    {
        $event = $this->_em->getRepository('SolustatTimeSheetBundle:Event')->find($id);
        $event->setLinked(0);
        $this->_em->persist($event);
        $this->_em->flush();
        $this->_em->clear();

        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function linkEvent($id, $user)
    {
        $event = $this->_em->getRepository('SolustatTimeSheetBundle:Event')->find($id);
        $event->setLinked(1);
        $event->setUser($user);
        $this->_em->persist($event);
        $this->_em->flush();
        $this->_em->clear();

        return true;
    }

    /**
     * @return mixed
     */
    public function getEventFree()
    {
        $events = $this->_em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->where('e.linked = :linked')
            ->setParameter('linked',0)
            ->getQuery()
            ->execute();

        return $events;
    }

    /**
     * @param int $limit
     * @param string $filter
     * @return mixed
     */
    public function getListAlerts($limit, $filter)
    {
        $events = $this->_em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->where('e.linked = :linked')
            ->setParameter('linked', 0)
            ->getQuery()
            ->execute();

        return $events;
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function getAllEventsPerUser(User $user) {
        $events = $this->_em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->select(['e.dateKey','e.intervalVisitTime'])
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        return $events;
    }

    /**
     * @param User $user
     * @param $time
     * @return array
     */
    public function getEventByTimeUser(User $user, $time) {
        $timeStart = new \DateTime($time.' 0:00:00');
        $timeEnd = new \DateTime($time.' 23:59:59');

        $events = $this->_em->getRepository('SolustatTimeSheetBundle:Event')
            ->createQueryBuilder('e')
            ->select(['e.dateKey','e.intervalVisitTime'])
            ->where('e.user = :user')
            ->andWhere('e.visitDate >= :timeStart')
            ->andWhere('e.visitDate < :timeEnd')
            ->setParameter('user', $user)
            ->setParameter('timeStart', $timeStart)
            ->setParameter('timeEnd', $timeEnd)
            ->getQuery()
            ->getArrayResult();

        return $events;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $dateTimeSetSerialize
     * @param $arrayEvents
     * @param $timeStampVisit
     * @param $startTimeStamp
     * @param $patientId
     * @return array|mixed
     */
    public function updateDateTimeSetBulk($dateTimeSetSerialize, $arrayEvents, $timeStampVisit, $startTimeStamp, $patientId)
    {
        if ($dateTimeSetSerialize) {
            $dateTimeSet = unserialize($dateTimeSetSerialize);
        } else {
            $dateTimeSet = [];
        }

        foreach($arrayEvents as $k=>$v) {

            if (isset($dateTimeSet[$v])) {
                $sizeIntervals = count($dateTimeSet[$v]);
                $i=0;

                if ($sizeIntervals == 1) {
                    $explode = explode('/', $dateTimeSet[$v][$i]);
                    list($start, $end) = preg_split("/-/", $explode[0]);
                    $dateTimeSet[$v][1] = $end . '-' . ($end + $timeStampVisit).'/'.$patientId;
                } elseif ($sizeIntervals == 0) {
                    $dateTimeSet[$v][0] = $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId;
                } else {
                    $flagDone = false;

                    while ($i < $sizeIntervals-1)
                    {
                        $explode = explode('/', $dateTimeSet[$v][$i]);
                        list($start, $end) = preg_split("/-/", $explode[0]);
                        $explode = explode('/', $dateTimeSet[$v][$i+1]);
                        list($startNext, $endNext) = preg_split("/-/", $explode[0]);

                        if ($end != $startNext && (($startNext-$end) >= $timeStampVisit)) {
                            array_splice($dateTimeSet[$v], $i+1, 0,  $end . '-' . ($end + $timeStampVisit).'/'.$patientId);
                            $flagDone = true;
                            break;
                        }
                        $i++;
                    }

                    if (!$flagDone){
                        array_push($dateTimeSet[$v], $endNext . '-' . ($endNext + $timeStampVisit).'/'.$patientId);
                    }
                }
            } else {
                $dateTimeSet[$v][0] = $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId;
            }
        }

        return $dateTimeSet;
    }

    /**
     * @param $dateTimeSetSerialize
     * @param $arrayEvents
     * @param $timeStampVisit
     * @param $startTimeStamp
     * @param $patientId
     * @return array|mixed
     */
    public function updateDateTimeSet($dateTimeSetSerialize, $arrayEvents, $timeStampVisit, $startTimeStamp, $patientId)
    {
        if ($dateTimeSetSerialize) {
            $dateTimeSet = unserialize($dateTimeSetSerialize);
        } else {
            $dateTimeSet = [];
        }

        foreach($arrayEvents as $k=>$v) {

            if (isset($dateTimeSet[$v])) {
                $sizeIntervals = count($dateTimeSet[$v]);
                $i=0;

                if ($sizeIntervals == 0) {
                    $dateTimeSet[$v][0] = $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId;
                } elseif($sizeIntervals == 1) {
                    $explode = explode('/', $dateTimeSet[$v][0]);
                    list($start, $end) = preg_split("/-/", $explode[0]);
                    if ($start >= $startTimeStamp){
                        array_splice($dateTimeSet[$v], 0, 0,  $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId);
                    } else {
                        array_splice($dateTimeSet[$v], 1, 0,  $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId);
                    }
                } else {
                    $flagDone = false;

                    while ($i < $sizeIntervals-1)
                    {
                        $explode = explode('/', $dateTimeSet[$v][$i]);
                        list($start, $end) = preg_split("/-/", $explode[0]);
                        $explode = explode('/', $dateTimeSet[$v][$i+1]);
                        list($startNext, $endNext) = preg_split("/-/", $explode[0]);

                        if ((int)$start >= $startTimeStamp) {
                            array_splice($dateTimeSet[$v], $i, 0,  $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId);
                            $flagDone = true;
                            break;
                        } elseif ($start < $startTimeStamp && $startTimeStamp <= $startNext) {
                            array_splice($dateTimeSet[$v], $i+1, 0,  $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId);
                            $flagDone = true;
                            break;
                        }
                        $i++;
                    }

                    if (!$flagDone){
                        array_push($dateTimeSet[$v], $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId);
                    }
                }
            } else {
                $dateTimeSet[$v][0] = $startTimeStamp . '-' . ($startTimeStamp + $timeStampVisit).'/'.$patientId;
            }
        }

        return $dateTimeSet;
    }

    /**
     * @param $dateTimeSetSerialize
     * @param $patientId
     * @return array|mixed
     */
    public function deleteDateTimeSetByPatientId($dateTimeSetSerialize, $patientId)
    {
        if ($dateTimeSetSerialize) {
            $dateTimeSet = unserialize($dateTimeSetSerialize);
        } else {
            $dateTimeSet = [];
        }


        foreach($dateTimeSet as &$date) {
            foreach ($date as $k => $v){
                if (explode('/', $v)[1] == $patientId) {
                    array_splice($date, $k,1);
                }
            }
        }

        return $dateTimeSet;
    }

    /**
     * @param $dateTimeSetSerialize
     * @param $arrayEvents
     * @param $visitTimeStamp
     * @param $event
     * @return array|mixed
     */
    public function deleteDateTimeByPatientIdAndTime($dateTimeSetSerialize, $arrayEvents, $visitTimeStamp, $event)
    {
        if ($dateTimeSetSerialize) {
            $dateTimeSet = unserialize($dateTimeSetSerialize);
        } else {
            $dateTimeSet = [];
        }

        $event = $this->_em->getRepository('SolustatTimeSheetBundle:Event')->find($event->getId());
        $startTimeStamp = $event->getVisitDate()->getTimeStamp()-strtotime($arrayEvents[0]);

        foreach ($dateTimeSet[$arrayEvents[0]] as $k=>$v){
            if ($startTimeStamp.'-'.($startTimeStamp + $visitTimeStamp).'/'.$event->getPatient()->getId() == $v) {
                array_splice($dateTimeSet[$arrayEvents[0]], $k,1);
            }
        }

        return $dateTimeSet;
    }

    /**
     * @param $numberVisitSetSerialize
     * @param $arrayEvents
     * @param $patientId
     * @return string
     */
    public function updateVisitTimeSet($numberVisitSetSerialize, $arrayEvents, $patientId)
    {
        if ($numberVisitSetSerialize) {
            $numberVisitSet = unserialize($numberVisitSetSerialize);
        } else {
            $numberVisitSet = [];
        }

        foreach($arrayEvents as $k=>$v) {
            $numberVisitSet[$v][] = $patientId;
        }

        return serialize($numberVisitSet);
    }

    /**
     * @param $numberVisitSetSerialize
     * @param $patientId
     * @return array
     */
    public function deleteVisitTimeBulk($numberVisitSetSerialize, $patientId)
    {
        if ($numberVisitSetSerialize) {
            $numberVisitSet = unserialize($numberVisitSetSerialize);
        } else {
            return false;
        }

        $arrayDateDelete = [];

        foreach ($numberVisitSet as $k=>$v) {
            if (($keys = array_keys($numberVisitSet[$k], $patientId)) !== false) {
                foreach ($keys as $key){
                    unset($numberVisitSet[$k][$key]);
                    $arrayDateDelete[] = $k;
                }

                sort($numberVisitSet[$k]);
            }
        }

        return [serialize($numberVisitSet),$arrayDateDelete];
    }

    /**
     * @param $numberVisitSetSerialize
     * @param $date
     * @param $patientId
     * @return bool|string
     */
    public function deleteVisitTime($numberVisitSetSerialize, $date, $patientId)
    {
        if ($numberVisitSetSerialize) {
            $numberVisitSet = unserialize($numberVisitSetSerialize);
        } else {
            return false;
        }

        if (($key = array_search($patientId, $numberVisitSet[$date])) !== false) {
            unset($numberVisitSet[$date][$key]);
            sort($numberVisitSet[$date]);
        }

        return serialize($numberVisitSet);
    }

    /**
     * @param $date
     * @param $numberVisitSet
     * @param $frequency
     * @return mixed
     */
    public function shiftDate($date, $numberVisitSet, $frequency)
    {
        if(!isset($numberVisitSet[$date])){
            return $date;
        } else {

            if (count($numberVisitSet[$date]) < self::MAXIMUM_VISIT_PER_DAY){
                return $date;
            } else {
                $time = $frequency->getTime();
                $nbRepetition = $frequency->getNbRepetition();
                $nbRepPerTime = $frequency->getNbRepPerTime();

                if($time != "day") {
                    if ($time == "week" && (int)$nbRepPerTime == 1) {
                        switch ((int)$nbRepetition) {
                            case 1:
                                $dateUpdated = $this->findDay($numberVisitSet, $date, self::AMPLITUDE_WEEK_PER_DAY_1);
                                break;
                            case 2:
                                $dateUpdated = $this->findDay($numberVisitSet, $date, self::AMPLITUDE_WEEK_PER_DAY_2);
                                break;
                            case 3:
                                $dateUpdated = $this->findDay($numberVisitSet, $date, self::AMPLITUDE_WEEK_PER_DAY_3);
                                break;
                            case 4:
                                $dateUpdated = $this->findDay($numberVisitSet, $date, self::AMPLITUDE_WEEK_PER_DAY_4);
                                break;
                            case 5:
                                $dateUpdated = $this->findDay($numberVisitSet, $date, self::AMPLITUDE_WEEK_PER_DAY_5);
                                break;
                        }

                        return $dateUpdated;
                    }

                    if ($time == "week" && (int)$nbRepPerTime == 2) {
                        return  $this->findDay($numberVisitSet, $date, self::AMPLITUDE_ONE_TIME_PER_2_WEEK_DAY);
                    }

                    if ($time == "week" && (int)$nbRepPerTime == 3) {
                        if((int)$nbRepetition ==1) {
                            return  $this->findDay($numberVisitSet, $date, self::AMPLITUDE_ONE_TIME_PER_3_WEEK_DAY);

                        } else {
                            return  $this->findDay($numberVisitSet, $date, self::AMPLITUDE_TWO_TIME_PER_3_WEEK_DAY);
                        }
                    }

                    if ($time == "month") {
                        return $this->findDay($numberVisitSet, $date, self::AMPLITUDE_MONTH);
                    }

                    if ($time == "year") {
                        return $this->findDay($numberVisitSet, $date, self::AMPLITUDE_YEAR);
                    }
                } else {
                    return $date;
                }
            }
        }
    }

    /**
     * @param $numberVisitSet
     * @param $date
     * @param $amplitude
     * @return string
     */
    public function findDay($numberVisitSet, $date, $amplitude){
        $dateTimeOrigin = new \DateTime($date);
        $i = 0;
        $arrayNumberOfDays = [];
        $we = 0;
        $arrayNumberOfDays[0] = count($numberVisitSet[$date]);

        do {
            $dateTime = clone $dateTimeOrigin;

            if ($dateTime->format('D') == 'Fri' && self::WEEKEND_OFF_SHIFTING_VISIT){
                $we = 2;
            }

            $string = '+'.($i + 1 + $we).' day';
            $result = $i + 1 + $we;
            $dateTime->modify($string);

            $test = $dateTime->format('D');

            if (isset($numberVisitSet[$dateTime->format('Y-m-d')])){
                $dateShifted = $numberVisitSet[$dateTime->format('Y-m-d')];
            }

            $arrayNumberOfDays[$result] = isset($dateShifted) ? count($dateShifted):0;
            $i++;
        } while ($i < $amplitude);

        $we = 0;
        $i = 0;

        if (self::IA_AMPLITUDE_NEGATIVE){
            do {
                $dateTime = clone $dateTimeOrigin;

                if ($dateTime->format('D') == 'Mon' && self::WEEKEND_OFF_SHIFTING_VISIT){
                    $we = 2;
                }

                $string = '-'.($i + 1 + $we).' day';
                $result = $i + 1 + $we;
                $dateTime->modify($string);

                $test = $dateTime->format('D');

                if(isset($numberVisitSet[$dateTime->format('Y-m-d')])){
                    $dateShifted = $numberVisitSet[$dateTime->format('Y-m-d')];
                }

                $arrayNumberOfDays[-$result] = isset($dateShifted) ? count($dateShifted):0;
                $i++;
            } while ($i < $amplitude);
        }

        //on prends le premier des minimums du tableau pour que cela soit le plus proche
        ksort($arrayNumberOfDays);
        $min  = min(array_values($arrayNumberOfDays));
        $keys = array_keys($arrayNumberOfDays, $min);
        $smallest = [];

        foreach ($keys as $key) {
                $smallest[$key] = abs($key - 0);
        }
        asort($smallest);

        $first = key($smallest);
        next($smallest);
        $second = key($smallest);

        if(count($smallest) > 1 && !is_null($second)){
            if(abs($first) == abs($second)){
                $rand = [$first,$second];
                shuffle($rand);
                $minimumVisitInTheDayKey = $rand[0];
            } else {
                $minimumVisitInTheDayKey = $first;
            }
        } else {
            $minimumVisitInTheDayKey = $first;
        }

        //Si le nombre minimum de visite des jours suivants sont egales alors on laisse le jour actuel
        if ($arrayNumberOfDays[$minimumVisitInTheDayKey] == count($numberVisitSet[$dateTimeOrigin->format('Y-m-d')]))
            return $dateTimeOrigin->format('Y-m-d');

        if ($minimumVisitInTheDayKey < 0){
            return $dateTimeOrigin->modify($minimumVisitInTheDayKey.' day')->format('Y-m-d');
        } else {
            return $dateTimeOrigin->modify('+'.$minimumVisitInTheDayKey.' day')->format('Y-m-d');
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function resync(User $user){
        return true;
    }
}
