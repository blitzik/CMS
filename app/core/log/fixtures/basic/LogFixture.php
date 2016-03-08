<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Log\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Log\EventLog;

class LogFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $event_creation = new EventLog(EventLog::CREATION);
        $manager->persist($event_creation);

        $event_editing = new EventLog(EventLog::EDITING);
        $manager->persist($event_editing);

        $event_removal = new EventLog(EventLog::REMOVAL);
        $manager->persist($event_removal);

        $manager->flush();
    }

}