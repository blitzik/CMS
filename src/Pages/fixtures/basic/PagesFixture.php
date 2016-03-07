<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Pages\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Url\Generators\UrlGenerator;

class PagesFixture extends AbstractFixture
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
        // FRONTEND
        $mainPage = UrlGenerator::create(' ', 'Pages:Front:Page', 'default'); // empty string in urlPath
        $manager->persist($mainPage);

        $searchByTag = UrlGenerator::create('search', 'Pages:Front:Search', 'tag'); // empty string in urlPath
        $manager->persist($searchByTag);



        // ADMINISTRATION
        $pageCreation = UrlGenerator::create('administration/article-creation', 'Pages:Admin:Page', 'new');
        $manager->persist($pageCreation);

        $pageEditing = UrlGenerator::create('administration/article-editing', 'Pages:Admin:Page', 'edit');
        $manager->persist($pageEditing);

        $pageRemoval = UrlGenerator::create('administration/article-removal', 'Pages:Admin:Page', 'remove');
        $manager->persist($pageRemoval);

        $pageOverview = UrlGenerator::create('administration/articles-overview', 'Pages:Admin:Page', 'overview');
        $manager->persist($pageOverview);

        $tags = UrlGenerator::create('administration/tags', 'Tags:Tag', 'default');
        $manager->persist($tags);
    }


}