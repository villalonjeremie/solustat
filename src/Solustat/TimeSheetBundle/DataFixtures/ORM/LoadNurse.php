<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\Nurse;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadNurse extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $nurses = array(
            array('surname' => 'Boudreaux',
                'name' => 'Melodie',
                'post' => 'Infirmiere',
                'registration_nb' => '12345',
                'security_level' => 'admin-user',
                'tel_work' => '546217983',
                'tel_mobile' => '546217984',),
            array('surname' => 'Boud',
                'name' => 'Melo',
                'post' => 'Auxilliaire',
                'registration_nb' => '56789',
                'security_level' => 'user',
                'tel_work' => '546217988',
                'tel_mobile' => '546217989',),
        );

        foreach ($nurses as $nurse) {
            $n = new Nurse();
            $n->setSurname($nurse['surname']);
            $n->setName($nurse['name']);
            $n->setPost($nurse['post']);
            $n->setRegistrationNb($nurse['registration_nb']);
            $n->setSecurityLevel($nurse['security_level']);
            $n->setTelWork($nurse['tel_work']);
            $n->setTelMobile($nurse['tel_mobile']);
            $n->setUpdatedAt(new \Datetime());
            $n->setSector($this->getReference(LoadSector::SECTOR_REFERENCE));

            $manager->persist($nurse);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            LoadSector::class,
        );
    }
}