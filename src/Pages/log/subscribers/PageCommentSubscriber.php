<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Pages\Log\Subscribers;

use Comments\Facades\CommentFacade;
use Log\Services\AppEventLogger;
use Kdyby\Events\Subscriber;
use Nette\Application\LinkGenerator;
use Nette\Security\User;
use Comments\Comment;
use Nette\Object;

class PageCommentSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        LinkGenerator $linkGenerator,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user;
        $this->linkGenerator = $linkGenerator;
    }


    function getSubscribedEvents()
    {
        return [
            CommentFacade::class . '::onSuccessCommentCreation',
            CommentFacade::class . '::onSuccessCommentRemoval',
            CommentFacade::class . '::onSuccessCommentSuppression',
            CommentFacade::class . '::onSuccessCommentRelease'
        ];
    }


    public function onSuccessCommentCreation(Comment $comment)
    {
        $user = $this->user->getIdentity();
        $pageLink = $this->linkGenerator->link('Pages:Front:Page:show', ['internal_id' => $comment->getPageId()]);

        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     '%s of <a href="%s">Page [%s#%s]</a> <b>has ADDED</b> the <a href="%s#comment-%s">Comment [%s#]</a>',
                     ($this->user->isLoggedIn() and $comment->getPageAuthorId() === $user->getId()) ?'Author' : 'Visitor',
                     $pageLink,
                     $comment->getPageId(),
                     $comment->getPageTitle(),
                     $pageLink,
                     $comment->getId(),
                     $comment->getId()
                 ),
                 'page_comment_creation',
                 ($this->user->isLoggedIn() and $comment->getPageAuthorId() === $user->getId()) ? $user->getId() : null
             );
    }


    public function onSuccessCommentSuppression(Comment $comment)
    {
        /** @var \Users\User $user */
        $user = $this->user->getIdentity();
        $pageLink = $this->linkGenerator->link('Pages:Front:Page:show', ['internal_id' => $comment->getPageId()]);

        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has SUPPRESSED</b> the <a href="%s#comment-%s">Comment [%s#]</a> of Author [%s#] on the <a href="%s">Page [%s#%s]</a>',
                     $user->getId(),
                     $user->username,
                     $pageLink,
                     $comment->getId(),
                     $comment->getId(),
                     $comment->getAuthor(),
                     $pageLink,
                     $comment->getPageId(),
                     $comment->getPageTitle()
                 ),
                 'page_comment_suppression',
                 $user->getId()
             );
    }


    public function onSuccessCommentRelease(Comment $comment)
    {
        /** @var \Users\User $user */
        $user = $this->user->getIdentity();
        $pageLink = $this->linkGenerator->link('Pages:Front:Page:show', ['internal_id' => $comment->getPageId()]);

        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has RELEASED</b> the <a href="%s#comment-%s">Comment [%s#]</a> of Author [%s#] on the <a href="%s">Page [%s#%s]</a>',
                     $user->getId(),
                     $user->username,
                     $pageLink,
                     $comment->getId(),
                     $comment->getId(),
                     $comment->getAuthor(),
                     $pageLink,
                     $comment->getPageId(),
                     $comment->getPageTitle()
                 ),
                 'page_comment_release',
                 $user->getId()
             );
    }


    public function onSuccessCommentRemoval(Comment $comment, $id)
    {
        /** @var \Users\User $user */
        $user = $this->user->getIdentity();
        $pageLink = $this->linkGenerator->link('Pages:Front:Page:show', ['internal_id' => $comment->getPageId()]);

        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has REMOVED</b> the Comment [%s#] of Author [%s] on the <a href="%s">Page [%s#%s]</a>',
                     $user->getId(),
                     $user->username,
                     $id,
                     $comment->getAuthor(),
                     $pageLink,
                     $comment->getPageId(),
                     $comment->getPageTitle()
                 ),
                 'page_comment_release',
                 $user->getId()
             );
    }

}