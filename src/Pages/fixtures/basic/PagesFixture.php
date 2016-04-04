<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Pages\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Users\Authorization\AuthorizationRulesGenerator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Log\Services\EventLogGenerator;
use Users\Authorization\Privilege;
use Users\Authorization\Resource;
use Users\Fixtures\UsersFixture;
use Url\Generators\UrlGenerator;
use Log\LogType;

class PagesFixture extends AbstractFixture implements DependentFixtureInterface
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
        $siteMap = UrlGenerator::create('sitemap.xml', 'Pages:Front:Page', 'sitemap');
        $manager->persist($siteMap);

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


    private function loadDefaultLoggingEvents(ObjectManager $manager)
    {
        $elg = new EventLogGenerator(new LogType('page'), $manager);
        $elg->addEvent('page_creation')
            ->addEvent('page_editing')
            ->addEvent('page_removal')
            ->addEvent('page_release')
            ->addEvent('page_comments_closure')
            ->addEvent('page_comments_opening')
            ->addEvent('page_draft_creation')
            ->addEvent('page_draft_editing')
            ->addEvent('page_draft_removal');


        $elg->addLogType(new LogType('page_comment'))
            ->addEvent('page_comment_creation')
            ->addEvent('page_comment_removal')
            ->addEvent('page_comment_suppression')
            ->addEvent('page_comment_release');


        $elg->addLogType(new LogType('page_tag'))
            ->addEvent('page_tag_creation')
            ->addEvent('page_tag_editing')
            ->addEvent('page_tag_removal');
    }


    private function loadDefaultAuthorizatorRules(ObjectManager $manager)
    {
        // privileges
        $silence = new Privilege('silence');
        $manager->persist($silence);

        $release = new Privilege('release');
        $manager->persist($release);

        $viewSilenced = new Privilege('view_silenced');
        $manager->persist($viewSilenced);

        $respondOnSilenced = new Privilege('respond_on_silenced');
        $manager->persist($respondOnSilenced);

        $commentOnClosed = new Privilege('comment_on_closed');
        $manager->persist($commentOnClosed);

        $arg = new AuthorizationRulesGenerator(new Resource('page'), $manager);
        $arg->addDefinition($this->getReference('privilege_create'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_edit'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_remove'), $this->getReference('role_admin'));

        // comments
        $arg->addResource(new Resource('page_comment'))
            ->addDefinition($silence, $this->getReference('role_admin'))
            ->addDefinition($release, $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_remove'), $this->getReference('role_admin'))
            ->addDefinition($viewSilenced, $this->getReference('role_admin'))
            ->addDefinition($respondOnSilenced, $this->getReference('role_admin'));

        // page_comment_form
        $arg->addResource(new Resource('page_comment_form'))
            ->addDefinition($commentOnClosed, $this->getReference('role_admin'));

        // tags
        $arg->addResource(new Resource('page_tag'))
            ->addDefinition($this->getReference('privilege_create'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_edit'), $this->getReference('role_admin'))
            ->addDefinition($this->getReference('privilege_remove'), $this->getReference('role_admin'));
    }


    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }

}