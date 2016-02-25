<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use App\Components\BaseControl;
use Comments\Comment;
use Comments\Decorators\CommentDecorator;
use Comments\Facades\CommentFacade;
use Comments\Query\CommentQuery;
use Nette\Application\UI\Multiplier;
use Pages\Page;

class CommentsOverviewControl extends BaseControl
{
    /** @var ICommentControlFactory */
    private $commentControlFactory;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var array */
    private $comments = [];

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
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/commentsOverview.latte');

        $this->comments = $this->commentFacade
                               ->fetchComments(
                                   (new CommentQuery())
                                    ->withReactions()
                                    ->byPage($this->page->getId())
                                    ->indexedById()
                               )->toArray();

        $template->comments = $this->comments;
        $template->page = $this->page;

        $template->render();
    }

    
    protected function createComponentComment()
    {
        return new Multiplier(function ($commentId) {
            return $this->commentControlFactory->create($this->comments[$commentId]);
        });
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