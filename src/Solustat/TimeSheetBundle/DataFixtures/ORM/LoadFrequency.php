<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\Frequency;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadFrequency extends Fixture
{
    const FREQUENCY_REFERENCE = '6 time per year';

    public function load(ObjectManager $manager)
    {
        $frequencies = array(
            array('nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "day"),
            array('nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "day"),
            array('nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('nb_repetition' => '4',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('nb_repetition' => '5',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('nb_repetition' => '1',
                'nb_rep_per_time' => '2',
                'time' => "week"),
            array('nb_repetition' => '1',
                'nb_rep_per_time' => '3',
                'time' => "week"),
            array('nb_repetition' => '2',
                'nb_rep_per_time' => '3',
                'time' => "week"),
            array('nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "month"),
            array('nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "month"),
            array('nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "month"),
            array('nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('nb_repetition' => '4',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('nb_repetition' => '5',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('nb_repetition' => '6',
                'nb_rep_per_time' => '1',
                'time' => "year"),
        );

        foreach ($frequencies as $frequency) {
            $f = new Frequency();
            $f->setNbRepetition($frequency['nb_repetition']);
            $f->setNbRepPerTime($frequency['nb_rep_per_time']);
            $f->setTime($frequency['time']);
            $manager->persist($frequency);
            $this->addReference(self::FREQUENCY_REFERENCE, $f);
        }

        $manager->flush();
    }
}