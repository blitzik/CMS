<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 28.02.2016
 */

namespace Pages\Components;

use Comments\Comment;
use Nette\Object;

class CommentsOrderList extends Object
{
    /** @var array */
    private $orderList = [];

    /** @var int */
    private $counter;


    public function __construct()
    {
        $this->counter = 1;
    }


    /**
     * @param Comment $comment
     */
    public function addComment(Comment $comment)
    {
        if (isset($this->orderList[$comment->getId()])) {
            return;
        }

        $this->orderList[$comment->getId()] = $this->counter;
        $this->counter++;
    }


    /**
     * @param Comment $comment
     * @return int|null Returns null if there is no record
     */
    public function getOrder(Comment $comment)
    {
        if (!isset($this->orderList[$comment->getId()])) {
            return null;
        }

        return $this->orderList[$comment->getId()];
    }
}