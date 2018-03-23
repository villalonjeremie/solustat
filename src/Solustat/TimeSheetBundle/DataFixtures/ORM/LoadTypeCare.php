<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\TypeCare;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadTypeCare extends Fixture
{
    const TYPE_CARE_REFERENCE = 'Autres soins';

    public function load(ObjectManager $manager)
    {
        $cares = array(
            array('name' => 'Injection',
                'color' => 'orange'),
            array('name' => 'Stomie',
                'color' => 'brown'),
            array('name' => 'Soins Cathéter',
                'color' => 'violet'),
            array('name' => 'Loi 90',
                'color' => 'green'),
            array('name' => 'Ouverture Dossier',
                'color' => 'yellow'),
            array('name' => 'Suivi Ponctuel',
                'color' => 'pink'),
            array('name' => 'Pansement',
                'color' => 'dark-blue'),
            array('name' => 'Soin Palliatif',
                'color' => 'light-blue'),
            array('name' => 'Autres soins',
                'color' => 'grey')
        );

        foreach ($cares as $care) {
            $tc = new TypeCare();
            $tc->setName($care['name']);
            $tc->setColor($care['color']);
            $manager->persist($tc);
            $this->setReference(self::TYPE_CARE_REFERENCE, $tc);
        }

        $manager->flush();
    }
}
