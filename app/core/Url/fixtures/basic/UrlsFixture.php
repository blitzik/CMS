<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Url\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Log\EventLog;
use Log\LogType;

class UrlsFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $logType = new LogType('url');
        $manager->persist($logType);

        $logEvent_404 = new EventLog('404');
        $manager->persist($logEvent_404);

        $manager->flush();
    }


    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }


}