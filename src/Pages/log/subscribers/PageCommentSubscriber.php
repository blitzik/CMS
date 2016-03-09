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
use Nette\Security\User;
use Comments\Comment;
use Nette\Object;

class PageCommentSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user;
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
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     '%s of page [%s#%s] has ADDED Comment [%s#]',
                     ($this->user->isLoggedIn() and $comment->getPageAuthorId() === $user->getId()) ?'Author' : 'Visitor',
                     $comment->getPageId(),
                     $comment->getPageTitle(),
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
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has SUPPRESSED the Comment [%s#] of Author [%s#] on the Page [%s#%s]',
                     $user->getId(),
                     $user->username,
                     $comment->getId(),
                     $comment->getAuthor(),
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
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has RELEASED the Comment [%s#] of Author [%s#] on the Page [%s#%s]',
                     $user->getId(),
                     $user->username,
                     $comment->getId(),
                     $comment->getAuthor(),
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
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has REMOVED the Comment [%s#] of Author [%s] on the Page [%s#%s]',
                     $user->getId(),
                     $user->username,
                     $id,
                     $comment->getAuthor(),
                     $comment->getPageId(),
                     $comment->getPageTitle()
                 ),
                 'page_comment_release',
                 $user->getId()
             );
    }

}