<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace Pages\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Users\Authorization\AccessDefinition;
use Users\Authorization\Permission;
use Users\Authorization\Privilege;
use Users\Authorization\Resource;
use Users\Fixtures\UsersFixture;
use Url\Generators\UrlGenerator;
use Log\EventLog;
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
        $pageType = new LogType('page');
        $manager->persist($pageType);

        $commentType = new LogType('page_comment');
        $manager->persist($commentType);

        $tagType = new LogType('page_tag');
        $manager->persist($tagType);


        // save & publish
        $pageCreation = new EventLog('page_creation', $pageType);
        $manager->persist($pageCreation);

        $pageEditing = new EventLog('page_editing', $pageType);
        $manager->persist($pageEditing);

        $pageRemoval = new EventLog('page_removal', $pageType);
        $manager->persist($pageRemoval);

        $pageRelease = new EventLog('page_release', $pageType);
        $manager->persist($pageRelease);

        $pageCommentsClosure = new EventLog('page_comments_closure', $pageType);
        $manager->persist($pageCommentsClosure);

        $pageCommentsOpening = new EventLog('page_comments_opening', $pageType);
        $manager->persist($pageCommentsOpening);


        // drafts
        $draftCreation = new EventLog('page_draft_creation', $pageType);
        $manager->persist($draftCreation);

        $draftEditing = new EventLog('page_draft_editing', $pageType);
        $manager->persist($draftEditing);

        $draftRemoval = new EventLog('page_draft_removal', $pageType);
        $manager->persist($draftRemoval);


        // comments
        $commentCreation = new EventLog('page_comment_creation', $commentType);
        $manager->persist($commentCreation);

        /*$commentEditing = new EventLog('page_comment_editing', $commentType);
        $manager->persist($commentEditing);*/

        $commentRemoval = new EventLog('page_comment_removal', $commentType);
        $manager->persist($commentRemoval);

        $commentSuppression = new EventLog('page_comment_suppression', $commentType);
        $manager->persist($commentSuppression);

        $commentRelease = new EventLog('page_comment_release', $commentType);
        $manager->persist($commentRelease);


        // tags
        $tagCreation = new EventLog('page_tag_creation', $tagType);
        $manager->persist($tagCreation);

        $tagEditing = new EventLog('page_tag_editing', $tagType);
        $manager->persist($tagEditing);

        $tagRemoval = new EventLog('page_tag_removal', $tagType);
        $manager->persist($tagRemoval);
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


        // Page
        $pageResource = new Resource('page');
        $manager->persist($pageResource);

        $permPageCreate = new Permission($this->getReference('role_admin'), $pageResource, $this->getReference('privilege_create'));
        $manager->persist($permPageCreate);

        $permPageEdit = new Permission($this->getReference('role_admin'), $pageResource, $this->getReference('privilege_edit'));
        $manager->persist($permPageEdit);

        $permPageRemove = new Permission($this->getReference('role_admin'), $pageResource, $this->getReference('privilege_remove'));
        $manager->persist($permPageRemove);


        // comments
        $commentResource = new Resource('page_comment');
        $manager->persist($commentResource);

        $permCommentSilence = new Permission($this->getReference('role_admin'), $commentResource, $silence);
        $manager->persist($permCommentSilence);

        $permCommentRelease = new Permission($this->getReference('role_admin'), $commentResource, $release);
        $manager->persist($permCommentRelease);

        $permCommentRemove = new Permission($this->getReference('role_admin'), $commentResource, $this->getReference('privilege_remove'));
        $manager->persist($permCommentRemove);

        $permSilencedComment = new Permission($this->getReference('role_admin'), $commentResource, $viewSilenced);
        $manager->persist($permSilencedComment);

        $permRespondOnSilenced = new Permission($this->getReference('role_admin'), $commentResource, $respondOnSilenced);
        $manager->persist($permRespondOnSilenced);


        $commentForm = new Resource('page_comment_form');
        $manager->persist($commentForm);

        $permCommentOnClosed = new Permission($this->getReference('role_admin'), $commentForm, $commentOnClosed);
        $manager->persist($permCommentOnClosed);


        // tags
        $tagResource = new Resource('page_tag');
        $manager->persist($tagResource);

        $permTagCreate = new Permission($this->getReference('role_admin'), $tagResource, $this->getReference('privilege_create'));
        $manager->persist($permTagCreate);

        $permTagEdit = new Permission($this->getReference('role_admin'), $tagResource, $this->getReference('privilege_edit'));
        $manager->persist($permTagEdit);

        $permTagRemove = new Permission($this->getReference('role_admin'), $tagResource, $this->getReference('privilege_remove'));
        $manager->persist($permTagRemove);



        // access definitions

        // page
        $acPageCreate = new AccessDefinition($pageResource, $this->getReference('privilege_create'));
        $manager->persist($acPageCreate);

        $acPageEdit = new AccessDefinition($pageResource, $this->getReference('privilege_edit'));
        $manager->persist($acPageEdit);

        $acPageRemove = new AccessDefinition($pageResource, $this->getReference('privilege_remove'));
        $manager->persist($acPageRemove);

        // comments
        $acCommentSilence = new AccessDefinition($commentResource, $silence);
        $manager->persist($acCommentSilence);

        $acCommentRelease = new AccessDefinition($commentResource, $release);
        $manager->persist($acCommentRelease);

        $acCommentRemove = new AccessDefinition($commentResource, $this->getReference('privilege_remove'));
        $manager->persist($acCommentRemove);

        $acCommentViewSilenced = new AccessDefinition($commentResource, $viewSilenced);
        $manager->persist($acCommentViewSilenced);

        $acCommentRespondOnSilenced = new AccessDefinition($commentResource, $respondOnSilenced);
        $manager->persist($acCommentRespondOnSilenced);

        $acCommentOnClosed = new AccessDefinition($commentForm, $commentOnClosed);
        $manager->persist($acCommentOnClosed);

        // tags
        $acTagCreate = new AccessDefinition($tagResource, $this->getReference('privilege_create'));
        $manager->persist($acTagCreate);

        $acTagEdit = new AccessDefinition($tagResource, $this->getReference('privilege_edit'));
        $manager->persist($acTagEdit);

        $acTagRemove = new AccessDefinition($tagResource, $this->getReference('privilege_remove'));
        $manager->persist($acTagRemove);
    }


    function getDependencies()
    {
        return [
            UsersFixture::class
        ];
    }

}