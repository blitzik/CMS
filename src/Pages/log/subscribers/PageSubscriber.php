<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Pages\Log\Subscribers;

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

    /** @var \Users\User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user->getIdentity();
    }


    function getSubscribedEvents()
    {
        return [
            PagePersister::class . '::onSuccessPageCreation',
            PagePersister::class . '::onSuccessPageEditing',
            PageFacade::class . '::onSuccessPageRemoval'
        ];
    }


    public function onSuccessPageCreation(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has CREATED Page%s [%s#%s]',
                     $page->getAuthorId(),
                     $page->getAuthorName(),
                     ($page->isDraft() ? ' draft' : ''),
                     $page->getId(),
                     $page->title
                 ),
                 $page->isDraft() ? 'page_draft_creation' : 'page_creation',
                 $page->getAuthorId()
             );
    }


    public function onSuccessPageEditing(Page $page)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has UPDATED Page%s [%s#%s]',
                     $page->getAuthorId(),
                     $page->getAuthorName(),
                     ($page->isDraft() ? ' draft' : ''),
                     $page->getId(),
                     $page->title
                 ),
                 $page->isDraft() ? 'page_draft_editing' : 'page_editing',
                 $page->getAuthorId()
             );
    }


    public function onSuccessPageRemoval(Page $page, $pageID)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has REMOVED Page%s [%s#%s]',
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
}