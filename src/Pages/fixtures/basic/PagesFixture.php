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
use Log\EventLog;
use Log\LogType;

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
        $this->loadDefaultLoggingEvents($manager);

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


}