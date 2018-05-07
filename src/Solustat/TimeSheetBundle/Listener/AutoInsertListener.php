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
        $entity = $args->getEntity();

        if (!$entity instanceof Patient) {
            return;
        }

        $entityVisitTime = $entity->getVisitTime();
       //$entity->get


       // $patients = $em->getRepository('SolustatTimeSheetBundle:Event')
         //   ->insertBulkEvents([1,2,3],$entity,$entityVisitTime);


     //   var_dump($args);
    }
}