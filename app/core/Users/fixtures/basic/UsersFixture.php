<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Users\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Url\Generators\UrlGenerator;
use Users\Authorization\Role;
use Log\EventLog;
use Log\LogType;
use Users\User;

class UsersFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUserRoles($manager);
        $this->loadDefaultUsers($manager);
        $this->loadDefaultLoggingEvents($manager);
        $this->loadDefaultUrls($manager);

        $manager->flush();
    }


    private function loadDefaultUsers(ObjectManager $manager)
    {
        $admin = new User('admin', 'admin@cms.cz', 'admin');
        $admin->addRole($this->getReference('role_admin'));
        $manager->persist($admin);
    }


    private function loadDefaultUserRoles(ObjectManager $manager)
    {
        $guest = new Role('guest');
        $manager->persist($guest);

        $user = new Role('user', $guest);
        $manager->persist($user);

        $admin = new Role('admin', $user);
        $manager->persist($admin);

        $this->addReference('role_guest', $guest);
        $this->addReference('role_user', $user);
        $this->addReference('role_admin', $admin);
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        $login = UrlGenerator::create('administration/login', 'Users:Auth', 'login');
        $manager->persist($login);

        $logout = UrlGenerator::create('administration/logout', 'Users:Auth', 'logout');
        $manager->persist($logout);
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        // Log types
        $userLogType = new LogType('user');
        $manager->persist($userLogType);

        // Log events
        $userLoginEvent = new EventLog('user_login', $userLogType);
        $manager->persist($userLoginEvent);

        $userLogoutEvent = new EventLog('user_logout', $userLogType);
        $manager->persist($userLogoutEvent);
    }

}