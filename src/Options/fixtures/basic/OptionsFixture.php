<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Options\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Options\Option;
use Url\Generators\UrlGenerator;

class OptionsFixture extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadDefaultUrls($manager);

        $blog_title = new Option('blog_title', 'Blog title');
        $manager->persist($blog_title);

        $blog_subtitle = new Option('blog_subtitle', 'Lorem ipsum dolor sit Amet');
        $manager->persist($blog_subtitle);

        $articles_per_page = new Option('articles_per_page', '10');
        $manager->persist($articles_per_page);

        $copyright = new Option('copyright', 'blitzik CMS');
        $manager->persist($copyright);

        $manager->flush();
    }


    private function loadDefaultUrls(ObjectManager $manager)
    {
        // ADMINISTRATION
        $options = UrlGenerator::create('administration/options', 'Options:Options', 'default');
        $manager->persist($options);
    }


}