<?php

namespace Solustat\TimeSheetBundle\Listener;

use Doctrine\DBAL\Exception\DatabaseObjectNotFoundException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Solustat\TimeSheetBundle\Entity\Patient;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\HttpFoundation\Session\Session;

class AutoInsertListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entities['patient'] = $args->getEntity();

        if (!($entities['patient'] instanceof Patient)) {
            return;
        }

        $em = $args->getEntityManager();
        $entities = [];

        if (!is_null($entities['patient']->getUpdatedAt())){
            return;
        }

        $entities['visit_time'] = $entities['patient']->getVisitTime();
        $entities['user'] = $entities['patient']->getUser();

        $em->getRepository('SolustatTimeSheetBundle:Event')
                    ->insertNewBulkEvents($entities['user'], $entities);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $session = new Session();
        $em = $args->getEntityManager();
        $entities = [];
        $entities['patient'] = $args->getEntity();

        if (!$entities['patient'] instanceof Patient) {
            return;
        }

        $entities['visit_time'] = $entities['patient']->getVisitTime();
        $entities['user'] = $entities['patient']->getUser();

        if ($session->get('flagFrequencyModified')) {
            $result = $em->getRepository('SolustatTimeSheetBundle:Event')->deleteEventsFromUserId($entities['patient']->getId());

            if ($result){
                $em->getRepository('SolustatTimeSheetBundle:Event')->insertUpdateBulkEvents($entities['user'], $entities);
            } else {
                return;
            }
        }

        if ($session->get('flagUserModified')) {
            $queryToUpdate = $em->createQuery(
                'UPDATE SolustatTimeSheetBundle:Event ev SET ev.user = '.$entities['user']->getId().' WHERE ev.patient = :evId'
            )->setParameter("evId", $entities['patient']->getId());

            $resultQuery = $queryToUpdate->execute();

            $oldUser = $em->getRepository('SolustatTimeSheetBundle:User')->find($session->get('oldUserId'));
            $newUser = $em->getRepository('SolustatTimeSheetBundle:User')->find($session->get('newUserId'));

            $oldNumberVisitSet = $oldUser->getNumberVisitSet();
            $newNumberVisitSet = $newUser->getNumberVisitSet();

            $result = $em->getRepository('SolustatTimeSheetBundle:Event')->deleteVisitTimeBulk($oldNumberVisitSet, $entities['patient']->getId());
            $oldNumberVisitSetUpdated = $result[0];
            $dateDeleted = $result[1];
            $newNumberVisitSetUpdated = $em->getRepository('SolustatTimeSheetBundle:Event')->updateVisitTimeSet($newNumberVisitSet, $dateDeleted, $entities['patient']->getId());
            $oldUser->setNumberVisitSet($oldNumberVisitSetUpdated);
            $newUser->setNumberVisitSet($newNumberVisitSetUpdated);
            $em->persist($oldUser);
            $em->persist($newUser);
            $em->flush();
            $em->clear();
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entities = [];
        $entities['patient'] = $args->getEntity();

        if (!$entities['patient'] instanceof Patient) {
            return;
        }

        $queryToDelete = $em->createQuery(
            'DELETE FROM SolustatTimeSheetBundle:Event ev WHERE ev.patient = :evId'
        )->setParameter("evId", $entities['patient']->getId());

        $queryToDelete->execute();
    }
}