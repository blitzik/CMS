<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Components\Front;

use App\Components\BaseControl;
use Comments\Comment;

class CommentControl extends BaseControl
{
    /** @var Comment */
    private $comment;


    public function __construct(Comment $comment) {
        $this->comment = $comment;
    }


    public function render($order)
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/comment.latte');

        $template->comment = $this->comment;
        $template->order = $order;

        $template->render();
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