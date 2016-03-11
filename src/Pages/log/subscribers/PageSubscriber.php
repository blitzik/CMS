<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Pages\Log\Subscribers;

use Nette\Application\LinkGenerator;
use Pages\Services\PagePersister;
use Log\Services\AppEventLogger;
use Pages\Facades\PageFacade;
use Kdyby\Events\Subscriber;
use Nette\Security\User;
use Nette\Object;
use Pages\Page;

class PageSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var \Users\User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        LinkGenerator $linkGenerator,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->linkGenerator = $linkGenerator;
        $this->user = $user->getIdentity();
    }


    function getSubscribedEvents()
    {
        return [
            PagePersister::class . '::onSuccessPageCreation',
            PagePersister::class . '::onSuccessPageEditing',
            PagePersister::class . '::onPageCommentsOpening',
            PagePersister::class . '::onPageCommentsClosure',
            PagePersister::class . '::onPageRelease',
            PageFacade::class . '::onSuccessPageRemoval',
        ];
    }


    public function onSuccessPageCreation(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 $this->createLogMessage(
                     'User [%s#%s] <b>has CREATED</b> <a href="%s">Page%s [%s#%s]</a>',
                     $page
                 ),
                 $page->isDraft() ? 'page_draft_creation' : 'page_creation',
                 $page->getAuthorId()
             );
    }


    public function onSuccessPageEditing(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 $this->createLogMessage(
                     'User [%s#%s] <b>has UPDATED</b> <a href="%s">Page%s [%s#%s]</a>',
                     $page
                 ),
                 $page->isDraft() ? 'page_draft_editing' : 'page_editing',
                 $page->getAuthorId()
             );
    }


    public function onPageRelease(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 $this->createLogMessage(
                     'User [%s#%s] <b>has RELEASED</b> <a href="%s">Page%s [%s#%s]</a>',
                     $page
                 ),
                 'page_release',
                 $page->getAuthorId()
             );
    }


    public function onSuccessPageRemoval(Page $page, $pageID)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has REMOVED</b> Page%s [%s#%s]',
                     $page->getAuthorId(),
                     $page->getAuthorName(),
                     ($page->isDraft() ? ' draft' : ''),
                     $pageID,
                     $page->title
                 ),
                 $page->isDraft() ? 'page_draft_creation' : 'page_creation',
                 $page->getAuthorId()
             );
    }


    // comments


    public function onPageCommentsOpening(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 $this->createLogMessage(
                     'User [%s#%s] <b>has OPENED</b> comments on <a href="%s">Page%s [%s#%s]</a>',
                     $page
                 ),
                 'page_comments_opening',
                 $page->getAuthorId()
             );
    }


    public function onPageCommentsClosure(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 $this->createLogMessage(
                     'User [%s#%s] <b>has CLOSED</b> comments on <a href="%s">Page%s [%s#%s]</a>',
                     $page
                 ),
                 'page_comments_closure',
                 $page->getAuthorId()
             );
    }


    /**
     * @param string $formatString
     * @param Page $page
     * @return string
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    private function createLogMessage($formatString, Page $page)
    {
        return sprintf(
            $formatString,
            $page->getAuthorId(),
            $page->getAuthorName(),
            $this->linkGenerator->link('Pages:Front:Page:show', ['internal_id' => $page->getId()]),
            ($page->isDraft() ? ' draft' : ''),
            $page->getId(),
            $page->title
        );
    }

}