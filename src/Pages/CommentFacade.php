<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 24.02.2016
 */

namespace Comments\Facades;

use Comments\Comment;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\ResultSet;
use Nette\Object;
use Pages\Page;

class CommentFacade extends Object
{
    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
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
     * @param Page $page
     * @return Comment[]
     */
    public function findAll(Page $page)
    {
        $q = $this->em->createQuery(
            'SELECT c FROM ' . Comment::class . ' c INDEX BY c.id
             WHERE c.page = :page'
        )->setParameter('page', $page)
         ->getResult();

        /*$this->em->createQuery(
            'SELECT PARTIAL c.{id}, PARTIAL r.{id} FROM ' . Comment::class . ' c
             LEFT JOIN c.reactions r INDEX BY r.id
             WHERE c.page = :page'
        )->setParameter('page', $page)
         ->execute();*/

        return $q;
    }
}