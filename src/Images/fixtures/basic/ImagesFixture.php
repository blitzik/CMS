<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Images\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Users\Authorization\AccessDefinition;
use Users\Authorization\Permission;
use Users\Authorization\Resource;
use Url\Generators\UrlGenerator;
use Log\EventLog;
use Log\LogType;
use Users\Fixtures\UsersFixture;

class ImagesFixture extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUrls($manager);
        $this->loadDefaultLoggingEvents($manager);
        $this->loadDefaultAuthorizatorRules($manager);

        $manager->flush();
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        // ADMINISTRATION
        $images = UrlGenerator::create('administration/images', 'Images:Image', 'default');
        $manager->persist($images);
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        $imageType = new LogType('image');
        $manager->persist($imageType);

        $imageUpload = new EventLog('image_upload', $imageType);
        $manager->persist($imageUpload);

        $imageRemoval = new EventLog('image_removal', $imageType);
        $manager->persist($imageRemoval);
    }


    private function loadDefaultAuthorizatorRules(ObjectManager $manager)
    {
        $imageResource = new Resource('image');
        $manager->persist($imageResource);

        $imageUpload = new Permission($this->getReference('role_admin'), $imageResource, $this->getReference('privilege_upload'));
        $manager->persist($imageUpload);

        $imageRemove = new Permission($this->getReference('role_admin'), $imageResource, $this->getReference('privilege_remove'));
        $manager->persist($imageRemove);

        
        // access definitions
        $acUpload = new AccessDefinition($imageResource, $this->getReference('privilege_upload'));
        $manager->persist($acUpload);

        $acRemove = new AccessDefinition($imageResource, $this->getReference('privilege_remove'));
        $manager->persist($acRemove);
    }


    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }

}