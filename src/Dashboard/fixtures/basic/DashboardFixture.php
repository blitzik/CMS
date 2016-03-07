<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Dashboard\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Url\Generators\UrlGenerator;

class DashboardFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUrls($manager);

        $manager->flush();
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        // ADMINISTRATION
        $dashboardDefault = UrlGenerator::create('administration/dashboard', 'Dashboard:Dashboard', 'default');
        $manager->persist($dashboardDefault);
    }


}