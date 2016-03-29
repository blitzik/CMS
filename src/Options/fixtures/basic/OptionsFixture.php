<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Options\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Users\Authorization\AccessDefinition;
use Users\Authorization\Permission;
use Users\Authorization\Resource;
use Url\Generators\UrlGenerator;
use Users\Fixtures\UsersFixture;
use Options\Option;

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
        $optionsResource = new Resource('options');
        $manager->persist($optionsResource);

        $optionsEdit = new Permission($this->getReference('role_user'), $optionsResource, $this->getReference('privilege_edit'));
        $manager->persist($optionsEdit);

        // access definitions
        $acEdit = new AccessDefinition($optionsResource, $this->getReference('privilege_edit'));
        $manager->persist($acEdit);
    }


    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }

}