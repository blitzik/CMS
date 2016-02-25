<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Comments\Query;

use Comments\Comment;
use Kdyby\Persistence\Queryable;
use Kdyby\Doctrine\QueryObject;
use Kdyby;

class CommentQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    public function byPage($pageID)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($pageID) {
            $qb->andWhere('c.page = :pageID')
               ->setParameter('pageID', $pageID);
        };

        return $this;
    }


    public function withReactions()
    {
        $this->onPostFetch[] = function ($_, Queryable $repository, \Iterator $iterator) {
            $ids = array_keys(iterator_to_array($iterator, true));

            $repository->createQueryBuilder()
                       ->select('PARTIAL c.{id}, reactions')
                       ->from(Comment::class, 'c')
                       ->leftJoin('c.reactions', 'reactions', null, null, 'reactions.id')
                       ->andWhere('c.id IN (:ids)')
                       ->setParameter('ids', $ids)
                       ->getQuery()
                       ->getResult();
        };

        return $this;
    }


    public function indexedById()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->indexBy('c', 'c.id');
        };

        return $this;
    }


    protected function doCreateCountQuery(Queryable $repository)
    {
        // todo
    }


    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicQuery($repository->getEntityManager());
        $qb->select('c');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(Comment::class, 'c');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}