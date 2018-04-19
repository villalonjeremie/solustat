<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\Position;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadPosition extends Fixture
{
    const POSITION_REFERENCE = 'infirmier(ere)';

    public function load(ObjectManager $manager)
    {
        $positions = array(
            array('name' => 'infirmier(ere)'),
            array('name' => 'auxilliaire'),
        );

        foreach ($positions as $position) {
            $s = new Position();
            $s->setPosition($position['name']);
            $manager->persist($s);
            $this->setReference(self::POSITION_REFERENCE, $s);
        }

        $manager->flush();
    }
}