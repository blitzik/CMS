<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Users\Fixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Log\EventLog;
use Log\LogType;
use Url\Generators\UrlGenerator;
use Users\User;

class UsersFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $admin = new User('admin', 'admin@cms.cz', 'admin');

        $manager->persist($admin);


        // URLs
        $login = UrlGenerator::create('administration/login', 'Users:Auth', 'login');
        $manager->persist($login);

        $logout = UrlGenerator::create('administration/logout', 'Users:Auth', 'logout');
        $manager->persist($logout);

        // Log types
        $userLogType = new LogType('user');
        $manager->persist($userLogType);

        // Log events
        $userLoginEvent = new EventLog('user_login');
        $manager->persist($userLoginEvent);

        $userLogoutEvent = new EventLog('user_logout');
        $manager->persist($userLogoutEvent);


        $manager->flush();
    }


    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }


}