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
use Pages\Exceptions\Runtime\ActionFailedException;
use Pages\Page;
use Pages\Services\CommentPersister;
use Pages\Services\CommentRemover;

class CommentFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $commentsRepository;

    /** @var CommentPersister */
    private $commentPersister;

    /** @var CommentRemover */
    private $commentRemover;


    public function __construct(
        EntityManager $entityManager,
        CommentRemover $commentRemover,
        CommentPersister $commentPersister
    ) {
        $this->em = $entityManager;
        $this->commentRemover = $commentRemover;
        $this->commentPersister = $commentPersister;
        $this->commentsRepository = $this->em->getRepository(Comment::class);
    }


    /**
     * @param array $values
     * @return Comment
     * @throws ActionFailedException
     */
    public function save(array $values)
    {
        return $this->commentPersister->save($values);
    }


    /**
     * @param Comment $comment
     * @throws \Exception
     */
    public function update(Comment $comment)
    {
        try {
            $this->em->persist($comment)->flush();
        } catch (\Exception $e) {
            throw new ActionFailedException;
        }
    }


    /**
     * @param Comment $comment
     * @throws ActionFailedException
     */
    public function remove(Comment $comment)
    {
        $this->commentRemover->remove($comment);
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