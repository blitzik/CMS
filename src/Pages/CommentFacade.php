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
use Pages\Services\CommentPersister;

class CommentFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $commentsRepository;

    /** @var CommentPersister */
    private $commentPersister;


    public function __construct(
        EntityManager $entityManager,
        CommentPersister $commentPersister
    ) {
        $this->em = $entityManager;
        $this->commentPersister = $commentPersister;
        $this->commentsRepository = $this->em->getRepository(Comment::class);
    }


    /**
     * @param array $values
     * @return Comment
     * @throws \Exception
     */
    public function saveComment(array $values)
    {
        return $this->commentPersister->save($values);
    }


    /**
     * @param Comment $comment
     * @throws \Exception
     */
    public function updateComment(Comment $comment)
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