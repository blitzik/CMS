<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 24.02.2016
 */

namespace Comments\Facades;

use Comments\Comment;
use Comments\Query\CommentQuery;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\ResultSet;
use Nette\Object;
use Pages\Page;

class CommentFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $commentsRepository;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->commentsRepository = $this->em->getRepository(Comment::class);
    }


    /**
     * @param Comment $comment
     * @throws \Exception
     */
    public function saveComment(Comment $comment)
    {
        $this->em->persist($comment)->flush();
    }


    /**
     * @param CommentQuery $commentQuery
     * @return Comment|null
     */
    public function fetchComment(CommentQuery $commentQuery)
    {
        return $this->commentsRepository->fetchOne($commentQuery);
    }


    /**
     * @param CommentQuery $commentQuery
     * @return ResultSet
     */
    public function fetchComments(CommentQuery $commentQuery)
    {
        return $this->commentsRepository->fetch($commentQuery);
    }
}