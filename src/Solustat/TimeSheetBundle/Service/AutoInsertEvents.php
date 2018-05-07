<?php

namespace Solustat\TimeSheetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;


class AutoInsertEvents
{
    protected $em;
    private $container;


    public function __construct(EntityManager $entityManager, Container $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function findEvent() {
        $qb = $this->em->createQueryBuilder();

        $qb->select('e')
            ->from('SolustatTimeSheetBundle:Event', 'e');
//            ->where('e.roles LIKE :roles')
//            ->setParameter('roles', '%"' . $role . '"%');

        return $qb->getQuery()->getResult();
    }

}