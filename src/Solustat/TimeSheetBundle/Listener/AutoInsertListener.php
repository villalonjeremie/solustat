<?php

namespace Solustat\TimeSheetBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Solustat\TimeSheetBundle\Entity\Patient;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;

class AutoInsertListener
{

    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entities = [];
        $entities['patient'] = $args->getEntity();

        if (!$entities['patient'] instanceof Patient && (!is_null($entities['patient']->getUpdatedAt())) ) {
            return;
        }

        $entities['visit_time'] = $entities['patient']->getVisitTime();
        $entities['user'] = $entities['patient']->getUser();

       $patients = $em->getRepository('SolustatTimeSheetBundle:Event')
                    ->insertNewBulkEvents($entities);

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entities = [];
        $entities['patient'] = $args->getEntity();

        if (!$entities['patient'] instanceof Patient) {
            return;
        }

        $entities['visit_time'] = $entities['patient']->getVisitTime();
        $entities['user'] = $entities['patient']->getUser();

        $queryToDelete = $em->createQuery(
            'DELETE FROM SolustatTimeSheetBundle:Event ev WHERE ev.patient = :evId'
        )->setParameter("evId", $entities['patient']->getId());

        $resultQuery = $queryToDelete->execute();

        if ($resultQuery){
            $em->getRepository('SolustatTimeSheetBundle:Event')->insertUpdateBulkEvents($entities);
        } else {
            return;
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