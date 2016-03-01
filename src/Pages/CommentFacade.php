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
     * @param Comment $comment It's a new comment or reaction on given comment ids
     * @param array $commentIDs
     * @throws \Exception
     */
    public function saveComment(Comment $comment, array $commentIDs = [])
    {
        $this->em->persist($comment);

        if (!empty($commentIDs)) {
            $comments = $this->em->createQuery(
                'SELECT c FROM ' . Comment::class . ' c WHERE c.id IN (:ids)'
            )->setParameter('ids', $commentIDs)->execute();

            /** @var Comment $c */
            foreach ($comments as $c) {
                $c->addReaction($comment);
                $this->em->persist($c);
            }
        }

        $this->em->flush();
    }


    /**
     * @param int $commentId
     * @return Comment|void
     */
    public function hide($commentId)
    {
        /** @var Comment $comment */
        $comment = $this->commentsRepository->find($commentId);
        if ($comment === null) {
            return null;
        }

        $comment->hide();
        $this->em->persist($comment)->flush();

        return $comment;
    }


    /**
     * @param int $commentId
     * @return Comment|null
     */
    public function show($commentId)
    {
        /** @var Comment $comment */
        $comment = $this->commentsRepository->find($commentId);
        if ($comment === null) {
            return null;
        }

        $comment->show();
        $this->em->persist($comment)->flush();

        return $comment;
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