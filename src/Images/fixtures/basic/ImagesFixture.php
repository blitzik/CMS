<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Images\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Users\Authorization\AuthorizationRulesGenerator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Log\Services\EventLogGenerator;
use Users\Authorization\Resource;
use Users\Fixtures\UsersFixture;
use Url\Generators\UrlGenerator;
use Log\LogType;

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
        $elg = new EventLogGenerator(new LogType('image'), $manager);
        $elg->addEvent('image_upload')
            ->addEvent('image_removal');
    }


    private function loadDefaultAuthorizatorRules(ObjectManager $manager)
    {
        $arg = new AuthorizationRulesGenerator(new Resource('image'), $manager);
        $arg->addDefinition($this->getReference('privilege_upload'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_remove'), $this->getReference('role_admin'));
    }


    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }

}