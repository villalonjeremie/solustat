<?php
namespace Solustat\TimeSheetBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Solustat\TimeSheetBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LoadUser extends Fixture
{
    const USER_REFERENCE = 'villalon.jeremie@gmail.com';

    public function load(ObjectManager $manager)
    {
        $users = array(
            array(
                'username' => 'villalon.jeremie@gmail.com',
                'username_canonical' => 'villalon.jeremie@gmail.com',
                'email' => 'villalon.jeremie@gmail.com',
                'email_canonical' => 'villalon.jeremie@gmail.com',
                'enabled' => 1,
                'salt' => null,
                'password' => '$2y$13$RaLFpyubUB0tLQ64R60PKeZ.GNTYaK61uPmYZqbgBinthznkjWFM.',
                'last_login' => new \Datetime('2018-05-07 02:22:45'),
                'confirmation_token' => null,
                'password_request_at' => null,
                'roles' => array("ROLE_USER"),
                'name' => 'Jérémie',
                'surname' => 'Villalon',
                'registration_nb' => '1234',
                'tel_work' => '0647146170',
                'tel_mobile' => '0647146170',
                'created_at' => new \Datetime('2018-05-07 02:22:45'),
                'updated_at' => null,
            )
        );

        foreach ($users as $user) {
            $u = new User();
            $u->setUsername($user['username']);
            $u->setUsernameCanonical($user['username_canonical']);
            $u->setEmail($user['email']);
            $u->setEmailCanonical($user['email_canonical']);
            $u->setEnabled($user['enabled']);
            $u->setSalt($user['salt']);
            $u->setPassword($user['password']);
            $u->setLastLogin($user['last_login']);
            $u->setConfirmationToken($user['confirmation_token']);
            $u->setPasswordRequestedAt($user['password_request_at']);
            $u->setRoles($user['roles']);
            $u->setName($user['name']);
            $u->setSurname($user['surname']);
            $u->setRegistrationNb($user['registration_nb']);
            $u->setTelWork($user['tel_work']);
            $u->setTelMobile($user['tel_mobile']);
            $u->setCreatedAt($user['created_at']);
            $u->setUpdatedAt($user['updated_at']);
            $u->setSector($this->getReference(LoadSector::SECTOR_REFERENCE));
            $u->setPosition($this->getReference(LoadPosition::POSITION_REFERENCE));

            $manager->persist($u);
            $this->setReference(self::USER_REFERENCE, $u);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            LoadPosition::class,
            LoadSector::class
        );
    }
}