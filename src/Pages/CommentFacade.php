<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 24.02.2016
 */

namespace Comments\Facades;

use Pages\Exceptions\Runtime\ActionFailedException;
use Kdyby\Doctrine\EntityRepository;
use Pages\Services\CommentPersister;
use Pages\Services\CommentRemover;
use Kdyby\Doctrine\EntityManager;
use Comments\Query\CommentQuery;
use Kdyby\Doctrine\ResultSet;
use Comments\Comment;
use Nette\Object;

class CommentFacade extends Object
{
    public $onSuccessCommentCreation;
    public $onSuccessCommentRemoval;
    public $onSuccessCommentSuppression;
    public $onSuccessCommentRelease;

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
        $comment = $this->commentPersister->save($values);

        $this->onSuccessCommentCreation($comment);

        return $comment;
    }


    public function silenceComment(Comment $comment)
    {
        $comment->silence();
        $this->em->persist($comment)->flush();

        $this->onSuccessCommentSuppression($comment);
    }


    public function releaseComment(Comment $comment)
    {
        $comment->release();
        $this->em->persist($comment)->flush();

        $this->onSuccessCommentRelease($comment);
    }


    /**
     * @param Comment $comment
     * @throws ActionFailedException
     */
    public function remove(Comment $comment)
    {
        $id = $comment->getId();
        $this->commentRemover->remove($comment);

        $this->onSuccessCommentRemoval($comment, $id);
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