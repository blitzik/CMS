<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use Nette\Application\UI\Multiplier;
use Comments\Facades\CommentFacade;
use Comments\Query\CommentQuery;
use App\Components\BaseControl;
use Comments\Comment;
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

    /** @var Page */
    private $page;


    public function __construct(
        Page $page,
        CommentFacade $commentFacade,
        ICommentControlFactory $commentControlFactory
    ) {
        $this->page = $page;
        $this->commentFacade = $commentFacade;
        $this->commentControlFactory = $commentControlFactory;

        $this->orderList = new CommentsOrderList();
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/commentsOverview.latte');

        if (empty($this->comments)) {
            $this->comments = $this->commentFacade
                                   ->fetchComments(
                                       (new CommentQuery())
                                        ->withReactions()
                                        ->byPage($this->page->getId())
                                        ->indexedById()
                                   )->toArray();

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