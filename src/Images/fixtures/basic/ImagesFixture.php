<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Images\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Url\Generators\UrlGenerator;
use Log\EventLog;
use Log\LogType;

class ImagesFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUrls($manager);
        $this->loadDefaultLoggingTypes($manager);
        $this->loadDefaultLoggingEvents($manager);

        $manager->flush();
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        // ADMINISTRATION
        $images = UrlGenerator::create('administration/images', 'Images:Image', 'default');
        $manager->persist($images);
    }


    private function loadDefaultLoggingTypes(ObjectManager $manager)
    {
        $imageType = new LogType('image');
        $manager->persist($imageType);
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        $imageUpload = new EventLog('image_upload');
        $manager->persist($imageUpload);

        $imageRemoval = new EventLog('image_removal');
        $manager->persist($imageRemoval);
    }

}