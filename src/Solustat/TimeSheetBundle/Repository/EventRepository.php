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
        $this->setUser($user);
        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());
        $dateTimeSet = $user->getDateTimeSet();
        $timeStampVisit = $patient->getVisitTime()->getTimeStamp();
        $startTimeStamp = self::HOUR_START_DAY * 3600;
        $dateTimeSet = $this->updateDateTimeSetBulk($dateTimeSet, $arrayEvents, $timeStampVisit, $startTimeStamp, $patient->getId());
        $user->setDateTimeSet(serialize($dateTimeSet));
        $this->_em->persist($user);

        $arraySize = count($arrayEvents);
        $date = new \DateTime('now');

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
            $event->setNurse($entities['user']);
            $this->_em->persist($event);
        }

        try {
            $this->_em->flush();
            $this->_em->clear();
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
        $this->setUser($user);
        $arrayEvents = $this->getArrayEvents($patient->getStartingDate(), $patient->getFrequency());

        $dateTimeSet = $this->getUser()->getDateTimeSet();
        $dateTimeSetUpdated = serialize($this->deleteDateTimeSetByPatientId($dateTimeSet, $patient->getId()));

        $timeStampVisit = $patient->getVisitTime()->getTimeStamp();
        $startTimeStamp = self::HOUR_START_DAY * 3600;
        $user->setDateTimeSet(serialize($this->updateDateTimeSetBulk($dateTimeSetUpdated, $arrayEvents, $timeStampVisit, $startTimeStamp, $patient->getId())));
        $this->_em->persist($user);

        $arraySize = count($arrayEvents);
        $date = new \DateTime('now');

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
            $event->setNurse($entities['user']);
            $this->_em->persist($event);
        }

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

        $arrayEvents[0] = $startDate->format('Y-m-d');
        $dateTimeSet = $user->getDateTimeSet();
        $startTimeStamp = $startDate->getTimestamp()-strtotime($arrayEvents[0]);
        $user->setDateTimeSet(serialize($this->updateDateTimeSet($dateTimeSet, $arrayEvents, $visitTimeStamp, $startTimeStamp, $patient->getId())));
        $this->_em->persist($user);

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


        $arrayEvents[0] = $startDate->format('Y-m-d');
        $dateTimeSet = $user->getDateTimeSet();
        $startTimeStamp = $startDate->getTimestamp()-strtotime($arrayEvents[0]);

        $dateTimeSet = $this->deleteDateTimeByPatientIdAndTime($dateTimeSet, $arrayEvents , $visitTimeStamp, $event);
        $user->setDateTimeSet(serialize($this->updateDateTimeSet($dateTimeSet, $arrayEvents, $visitTimeStamp, $startTimeStamp, $filter['patientId'])));
        $this->_em->persist($user);

        $event->setVisitDate($startDate);
        $event->setUpdatedAt(new \DateTime('now'));
        $event->setVisitTime($visitTime[0]);
        $event->setAutoGenerate(0);
        $event->setLinked(1);
        $this->_em->persist($event);
        $this->_em->flush();
        $this->_em->clear();

        return $event;
    }

    /**
     * @param $id
     * @return int
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteEvent($id)
    {
        $event = $this->_em->getRepository('SolustatTimeSheetBundle:Event')->find($id);
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
            ->select('e.visitDate')
            ->where('e.user = :user')
            ->setParameter('user', $user)
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

    private function updateDateTimeSet($dateTimeSetSerialize, $arrayEvents, $timeStampVisit, $startTimeStamp, $patientId)
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

    private function deleteDateTimeSetByPatientId($dateTimeSetSerialize, $patientId) {
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
     * @param $patientId
     * @param $eventId
     * @return array|mixed
     */
    private function deleteDateTimeByPatientIdAndTime($dateTimeSetSerialize, $arrayEvents, $visitTimeStamp, $event) {
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
}
