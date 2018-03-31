<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\Frequency;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadFrequency extends Fixture
{
    const FREQUENCY_REFERENCE = '6 t per year';

    public function load(ObjectManager $manager)
    {
        $frequencies = array(
            array('name' => '1 fois par jour',
                'nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => 'day'),
            array('name' => '2 fois par jour',
                'nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "day"),
            array('name' => '3 fois par jour',
                'nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "day"),
            array('name' => '1 fois par semaine',
                'nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('name' => '2 fois par semaine',
                'nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('name' => '3 fois par semaine',
                'nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('name' => '4 fois par semaine',
                'nb_repetition' => '4',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('name' => '5 fois par semaine',
                'nb_repetition' => '5',
                'nb_rep_per_time' => '1',
                'time' => "week"),
            array('name' => '1 fois / 2 semaine',
                'nb_repetition' => '1',
                'nb_rep_per_time' => '2',
                'time' => "week"),
            array('name' => '1 fois / 3 semaine',
                'nb_repetition' => '1',
                'nb_rep_per_time' => '3',
                'time' => "week"),
            array('name' => '2 fois / 3 semaine',
                'nb_repetition' => '2',
                'nb_rep_per_time' => '3',
                'time' => "week"),
            array('name' => '1 fois par mois',
                'nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "month"),
            array('name' => '2 fois par mois',
                'nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "month"),
            array('name' => '3 fois par mois',
                'nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "month"),
            array('name' => '1 fois par an',
                'nb_repetition' => '1',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('name' => '2 fois par an',
                'nb_repetition' => '2',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('name' => '3 fois par an',
                'nb_repetition' => '3',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('name' => '4 fois par an',
                'nb_repetition' => '4',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('name' => '5 fois par an',
                'nb_repetition' => '5',
                'nb_rep_per_time' => '1',
                'time' => "year"),
            array('name' => '6 fois par an',
                'nb_repetition' => '6',
                'nb_rep_per_time' => '1',
                'time' => "year"),
        );

        foreach ($frequencies as $frequency) {
            $f = new Frequency();
            $f->setName($frequency['name']);
            $f->setNbRepetition($frequency['nb_repetition']);
            $f->setNbRepPerTime($frequency['nb_rep_per_time']);
            $f->setTime($frequency['time']);
            $manager->persist($f);
            $this->setReference(self::FREQUENCY_REFERENCE, $f);
        }

        $manager->flush();
    }
}