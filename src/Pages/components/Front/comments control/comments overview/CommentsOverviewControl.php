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
use Pages\Components\CommentsOrderList;
use Pages\Page;

class CommentsOverviewControl extends BaseControl
{
    /** @var ICommentControlFactory */
    private $commentControlFactory;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var CommentsOrderList */
    private $orderList;

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

        $this->orderList = new CommentsOrderList();
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/commentsOverview.latte');

        if (empty($this->comments)) {
            $query = (new CommentQuery())
                      ->byPage($this->page->getId())
                      ->indexedById();

            if ($this->user->isLoggedIn()) {
                $query->withReactions(false);
            } else {
                $query->withReactions();
                $query->onlyVisible();
            }

            $this->comments = $this->commentFacade
                                   ->fetchComments($query)->toArray();

            $this->fillOrderList($this->comments);
        }

        $template->comments = $this->comments;
        $template->page = $this->page;

        $template->render();
    }

    
    protected function createComponentComment()
    {
        return new Multiplier(function ($commentId) {
            if (!isset($this->comments)) {
                $comment = $this->getComment($commentId);
                if ($comment !== null) {
                    $this->comments[$commentId] = $comment;
                }
            }

            $comp = $this->commentControlFactory
                         ->create($this->comments[$commentId], $this->orderList);

            return $comp;
        });
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


    private function fillOrderList(array $comments)
    {
        /** @var Comment $comment */
        foreach ($comments as $comment) {
            $this->orderList->addComment($comment);
        }
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