<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 13.03.2016
 */

namespace Localization\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Localization\Locale;

class LocalizationFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $en_US = new Locale('en_US', 'en', 'English');
        $manager->persist($en_US);

        $cs_CZ = new Locale('cs_CZ', 'cs', 'Čeština', true);
        $manager->persist($cs_CZ);

        $manager->flush();
    }

}