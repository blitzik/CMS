<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use App\Components\BaseControl;
use blitzik\FlashMessages\FlashMessage;
use Comments\Facades\CommentFacade;
use Nette\Security\User;
use Comments\Comment;
use Pages\Components\CommentsOrderList;

class CommentControl extends BaseControl
{
    /** @var array */
    public $onSuccessCommentHide;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var User */
    private $user;

    /** @var CommentsOrderList */
    private $orderList;

    /** @var Comment */
    private $comment;


    public function __construct(
        Comment $comment,
        CommentsOrderList $commentsOrderList,
        CommentFacade $commentFacade,
        User $user
    ) {
        $this->comment = $comment;
        $this->orderList = $commentsOrderList;
        $this->commentFacade = $commentFacade;
        $this->user = $user;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/comment.latte');

        $template->comment = $this->comment;
        $template->getOrderNumber = function (Comment $comment) {
            return $this->orderList->getOrder($comment);
        };

        $template->render();
    }


    public function handleSilence()
    {
        $this->userPermissionCheck();

        $this->comment->silence();
        $this->commentFacade->saveComment($this->comment);

        $this->refresh('Text komentáře byl úspěšně potlačen', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleRelease()
    {
        $this->userPermissionCheck();

        $this->comment->release();
        $this->commentFacade->saveComment($this->comment);

        $this->refresh('Text komentáře byl úspěšně zobrazen', FlashMessage::SUCCESS, ['comment-text', 'comment-suppress']);
    }


    public function handleHide()
    {
        $this->userPermissionCheck();

        $this->commentFacade->hide($this->comment->getId());

        $this->refresh('Komentář byl úspěšně skryt', FlashMessage::SUCCESS, ['admin-meta', 'comment-hide']);
    }


    public function handleShow()
    {
        $this->userPermissionCheck();

        $this->commentFacade->show($this->comment->getId());

        $this->refresh('Komentář byl úspěšně zobrazen', FlashMessage::SUCCESS, ['admin-meta', 'comment-hide']);
    }


    private function userPermissionCheck()
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('K provedení akce nemáte dostatečná oprávnění', FlashMessage::WARNING);
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
            $this->redirect('this#' . $this->comment->getId());
        }
    }
}


interface ICommentControlFactory
{
    /**
     * @param Comment $comment
     * @param CommentsOrderList $orderList
     * @return CommentControl
     */
    public function create(Comment $comment, CommentsOrderList $orderList);
}