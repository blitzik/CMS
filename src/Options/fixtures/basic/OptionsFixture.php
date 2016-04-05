<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Options\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Users\Authorization\AuthorizationRulesGenerator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Log\Services\EventLogGenerator;
use Users\Authorization\Resource;
use Url\Generators\UrlGenerator;
use Users\Fixtures\UsersFixture;
use Options\Option;
use Log\LogType;

class OptionsFixture extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUrls($manager);
        $this->loadDefaultOptions($manager);
        $this->loadDefaultAuthorizatorRules($manager);
        $this->loadDefaultLoggingEvents($manager);

        $manager->flush();
    }


    private function loadDefaultOptions(ObjectManager $manager)
    {
        $blog_title = new Option('blog_title', 'Blog title');
        $manager->persist($blog_title);

        $blog_subtitle = new Option('blog_subtitle', 'Lorem ipsum dolor sit Amet');
        $manager->persist($blog_subtitle);

        $articles_per_page = new Option('articles_per_page', '20');
        $manager->persist($articles_per_page);

        $copyright = new Option('copyright', 'blitzik CMS');
        $manager->persist($copyright);

        $gaMeasureCode = new Option('google_analytics_measure_code', null);
        $manager->persist($gaMeasureCode);
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        // ADMINISTRATION
        $options = UrlGenerator::create('administration/options', 'Options:Options', 'default');
        $manager->persist($options);
    }


    private function loadDefaultAuthorizatorRules(ObjectManager $manager)
    {
        $arg = new AuthorizationRulesGenerator(new Resource('options'), $manager);
        $arg->addDefinition($this->getReference('privilege_edit'), $this->getReference('role_admin'));
    }


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        $elg = new EventLogGenerator(new LogType('options'), $manager);
        $elg->addEvent('options_editing');
    }


    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }

}