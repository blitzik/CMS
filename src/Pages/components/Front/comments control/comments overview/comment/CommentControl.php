<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use Pages\Exceptions\Runtime\ActionFailedException;
use blitzik\FlashMessages\FlashMessage;
use Comments\Facades\CommentFacade;
use App\Components\BaseControl;
use Nette\Security\User;
use Comments\Comment;

class CommentControl extends BaseControl
{
    /** @var array */
    public $onSuccessCommentRemoval;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var User */
    private $user;

    /** @var Comment */
    private $comment;


    public function __construct(
        Comment $comment,
        CommentFacade $commentFacade,
        User $user
    ) {
        $this->comment = $comment;
        $this->commentFacade = $commentFacade;
        $this->user = $user;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/comment.latte');

        $template->comment = $this->comment;

        $template->render();
    }


    public function handleSilence()
    {
        $this->userPermissionCheck();

        $this->commentFacade->silenceComment($this->comment);

        $this->refresh('page.comments.actions.silence.messages.success', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRelease()
    {
        $this->userPermissionCheck();

        $this->commentFacade->releaseComment($this->comment);

        $this->refresh('page.comments.actions.release.messages.success', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRemove()
    {
        $this->userPermissionCheck();

        try {
            $this->commentFacade->remove($this->comment);
        } catch (ActionFailedException $e) {
            $this->refresh('page.comments.actions.remove.messages.error', FlashMessage::ERROR);
        }

        $this->onSuccessCommentRemoval($this->comment);
        $this->redirect('this#comments');
    }


    private function userPermissionCheck()
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('page.comments.actions.messages.permission', FlashMessage::WARNING);
            $this->redirect('this#' . $this->comment->getId());
        }
    }


    private function refresh($message, $type, array $snippets = null)
    {
        if ($this->presenter->isAjax()) {
            if ($snippets === null) { return; }
            if (empty($snippets)) { $this->redrawControl(); return; }

            foreach ($snippets as $snippet) {
                $this->redrawControl($snippet);
            }
        } else {
            $this->flashMessage($message, $type);
            $this->redirect('this#comment-' . $this->comment->getId());
        }
    }
}


interface ICommentControlFactory
{
    /**
     * @param Comment $comment
     * @return CommentControl
     */
    public function create(Comment $comment);
}