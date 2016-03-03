<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 03.03.2016
 */

namespace Pages\Services;

use Kdyby\Doctrine\EntityManager;
use Comments\Comment;
use Nette\Object;
use Pages\Exceptions\Runtime\ActionFailedException;

class CommentRemover extends Object
{
    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @param Comment $comment
     * @throws ActionFailedException
     */
    public function remove(Comment $comment)
    {
        try {
            $this->em->beginTransaction();

            $this->em->remove($comment)->flush();
            $this->em->createQuery(
                'UPDATE ' . Comment::class . ' c SET c.order = c.order - 1
                 WHERE c.page = :page and c.order > :order'
            )->execute(['page' => $comment->getPageId(), 'order' => $comment->getOrder()]);

            $this->em->commit();

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            throw new ActionFailedException;
        }
    }
}