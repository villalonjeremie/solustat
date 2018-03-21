<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\VisitTime;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadVisitTime extends Fixture
{
    const  VISIT_TIME_REFERENCE = 'Soins normaux';

    public function load(ObjectManager $manager)
    {
        $visits = array(
            array('name' => 'Soins durée 1h'),
            array('name' => 'Soins normaux'),
        );

        foreach ($visits as $visit) {
            $v = new VisitTime();
            $v->setName($visit['name']);
            $manager->persist($v);
            $this->addReference(self::VISIT_TIME_REFERENCE, $v);
        }

        $manager->flush();
    }
}