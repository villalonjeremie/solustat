<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\Patient;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LoadPatient extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $patients = array(
            array('folder_number'=>'1',
                    'name' => 'Alexande',
                'surname' => 'Dupont',
                'address' => "4470 rue Fabre",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217982",
                'visit_time' => 'Soins durée 1h',
                'sector' => '2',
                'frequency' => '1'
            ),
            array('folder_number'=>'2',
                'name' => 'Alex',
                'surname' => 'Depon',
                'address' => "4470 rue Fabre",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217983",
                'visit_time' => 'Soins normaux',
                'sector' => '2',
                'type_care' => 'Stomie',
                'frequency' => '1'
            ),
            array('folder_number'=>'3',
                'name' => 'John',
                'surname' => 'Doe',
                'address' => "4469 rue Saint Denis",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217990",
                'visit_time' => 'Soins normaux',
                'sector' => '2',
                'type_care' => 'Stomie',
                'frequency' => '1'
            ),
            array('folder_number'=>'4',
                'name' => 'Sylvester',
                'surname' => 'Stallone',
                'address' => "4490 rue Saint Laurent",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217991",
                'visit_time' => 'Soins normaux',
                'sector' => '2',
                'type_care' => 'Stomie',
                'frequency' => '1'
            ),
            array('folder_number'=>'5',
                'name' => 'Coté',
                'surname' => 'Alex',
                'address' => "4470 rue Fabre",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217991",
                'visit_time' => 'Soins durée 1h',
                'sector' => '2',
                'type_care' => 'Injection',
                'frequency' => '1'
            ),
            array('folder_number'=>'6',
                'name' => 'Giroux',
                'surname' => 'Erika',
                'address' => "4470 rue Saint Catherine",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217992",
                'visit_time' => 'Soins normaux',
                'sector' => '2',
                'type_care' => 'Injection',
                'frequency' => '1'
            ),
            array('folder_number'=>'7',
                'name' => 'Cinq-mars',
                'surname' => 'Luc',
                'address' => "4470 rue Montroyal",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217982",
                'visit_time' => 'Soins durée 1h',
                'sector' => '2',
                'type_care' => 'Injection',
                'frequency' => '1'
            ),
            array('folder_number'=>'8',
                'name' => 'Couture',
                'surname' => 'Dominic',
                'address' => "4470 rue Fabre",
                'zip' => "H2J 3V2",
                'town' => "MONTREAL",
                'tel' => "5146217982",
                'visit_time' => 'Soins durée 1h',
                'sector' => '1',
                'type_care' => 'Injection',
                'frequency' => '1'
            ),
        );

        foreach ($patients as $patient) {
            $p = new Patient();

            $p->setFolderNumber($patient['folder_number']);
            $p->setName($patient['name']);
            $p->setSurname($patient['surname']);
            $p->setAddress($patient['address']);
            $p->setZip($patient['zip']);
            $p->setTown($patient['town']);
            $p->setTel($patient['tel']);
            $p->setCreatedAt(new \Datetime());
            $p->setUpdatedAt(new \Datetime());
            $p->setVisitTime($this->getReference(LoadVisitTime::VISIT_TIME_REFERENCE));
            $p->setSector($this->getReference(LoadSector::SECTOR_REFERENCE));
            $p->setTypeCare($this->getReference(LoadTypeCare::TYPE_CARE_REFERENCE));
            $p->setFrequency($this->getReference(LoadFrequency::FREQUENCY_REFERENCE));
            $manager->persist($p);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            LoadFrequency::class,
            LoadTypeCare::class,
            LoadSector::class,
            LoadVisitTime::class,
        );
    }
}