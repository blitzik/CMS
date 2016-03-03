<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use blitzik\FlashMessages\FlashMessage;
use Nette\Application\UI\Multiplier;
use Comments\Facades\CommentFacade;
use Comments\Query\CommentQuery;
use App\Components\BaseControl;
use Comments\Comment;
use Nette\Security\User;
use Pages\Page;

class CommentsOverviewControl extends BaseControl
{
    /** @var ICommentControlFactory */
    private $commentControlFactory;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var array */
    private $comments;

    /** @var User */
    private $user;

    /** @var Page */
    private $page;


    public function __construct(
        Page $page,
        User $user,
        CommentFacade $commentFacade,
        ICommentControlFactory $commentControlFactory
    ) {
        $this->page = $page;
        $this->user = $user;
        $this->commentFacade = $commentFacade;
        $this->commentControlFactory = $commentControlFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/commentsOverview.latte');

        if (empty($this->comments)) {
            $this->findComments();
        }

        $template->comments = $this->comments;
        $template->page = $this->page;

        $template->render();
    }

    
    protected function createComponentComment()
    {
        return new Multiplier(function ($commentId) {
            if (empty($this->comments)) {
                if ($this->presenter->isAjax()) {
                    $comment = $this->getComment($commentId);
                    if ($comment !== null) {
                        $this->comments[$commentId] = $comment;
                    }
                } else {
                    $this->findComments();
                }
            }

            $comp = $this->commentControlFactory
                         ->create($this->comments[$commentId]);

            $comp->onSuccessCommentRemoval[] = function (Comment $comment) {
                $this->flashMessage(sprintf('Komentář autora "%s" byl úspěšně odstraněn', $comment->getAuthor()), FlashMessage::SUCCESS);
            };

            return $comp;
        });
    }


    private function findComments()
    {
        $query = (new CommentQuery())
                  ->byPage($this->page->getId())
                  ->indexedById();

        if ($this->user->isLoggedIn()) {
            $query->withReactions(false);
        } else {
            $query->withReactions();
        }

        $this->comments = $this->commentFacade
                               ->fetchComments($query)->toArray();
    }


    /**
     * @param $commentId
     * @return Comment|null
     */
    private function getComment($commentId)
    {
        return $this->commentFacade
                    ->fetchComment(
                        (new CommentQuery())
                        ->byId($commentId)
                        ->byPage($this->page)
                        ->indexedById()
                    );
    }
}


interface ICommentsOverviewControlFactory
{
    /**
     * @param Page $page
     * @return CommentsOverviewControl
     */
    public function create(Page $page);
}