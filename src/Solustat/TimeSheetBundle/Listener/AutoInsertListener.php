<?php

namespace Solustat\TimeSheetBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Solustat\TimeSheetBundle\Entity\Patient;

class AutoInsertListener
{
    /**
     *
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entities = [];
        $entities['patient'] = $args->getEntity();

        if (!$entities['patient'] instanceof Patient) {
            return;
        }

        $entities['visit_time'] = $entities['patient']->getVisitTime();
        $entities['user'] = $entities['patient']->getUser();

       $patients = $em->getRepository('SolustatTimeSheetBundle:Event')
                    ->insertBulkEvents($entities);


        var_dump($args);
    }
}