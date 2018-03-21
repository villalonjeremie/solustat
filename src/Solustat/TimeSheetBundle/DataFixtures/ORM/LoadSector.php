<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\Sector;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadSector extends Fixture
{
    const SECTOR_REFERENCE = '5';

    public function load(ObjectManager $manager)
    {
        $sectors = array(
            array('name' => '1'),
            array('name' => '2'),
            array('name' => '3'),
            array('name' => '4'),
            array('name' => '5'),
        );

        foreach ($sectors as $sector) {
            $s = new Sector();
            $s->setName($sector['name']);
            $manager->persist($s);
            $this->addReference(self::SECTOR_REFERENCE, $s);
        }

        $manager->flush();
    }
}