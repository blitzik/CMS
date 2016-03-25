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
    private $userEntity;

    /** @var Comment */
    private $comment;


    public function __construct(
        Comment $comment,
        CommentFacade $commentFacade,
        User $user
    ) {
        $this->comment = $comment;
        $this->commentFacade = $commentFacade;
        $this->userEntity = $user;
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
        $this->userPermissionCheck('silence');

        $this->commentFacade->silenceComment($this->comment);

        $this->refresh('page.comments.actions.silence.messages.success', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRelease()
    {
        $this->userPermissionCheck('release');

        $this->commentFacade->releaseComment($this->comment);

        $this->refresh('page.comments.actions.release.messages.success', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRemove()
    {
        $this->userPermissionCheck('remove');

        try {
            $this->commentFacade->remove($this->comment);
        } catch (ActionFailedException $e) {
            $this->refresh('page.comments.actions.remove.messages.error', FlashMessage::ERROR);
        }

        $this->onSuccessCommentRemoval($this->comment);
        $this->redirect('this#comments');
    }


    private function userPermissionCheck($action)
    {
        if (!$this->user->isAllowed('page_comment', $action)) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            $this->redirect('this#comment-' . $this->comment->getId());
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