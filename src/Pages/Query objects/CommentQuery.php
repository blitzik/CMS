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


    public function byId($commentId)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($commentId) {
            $qb->andWhere('c.id = :id')
                ->setParameter('id', $commentId);
        };

        return $this;
    }


    public function byPage($pageID)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($pageID) {
            $qb->andWhere('c.page = :pageID')
               ->setParameter('pageID', $pageID);
        };

        return $this;
    }


    public function onlyVisible()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('c.isHidden = 0');
        };

        return $this;
    }


    public function withReactions($onlyVisible = true)
    {
        /*$this->onPostFetch[] = function ($_, Queryable $repository, \Iterator $iterator) {
            $ids = array_keys(iterator_to_array($iterator, true));

            $repository->createQueryBuilder()
                            ->select('PARTIAL c.{id}, reaction')
                            ->from(Comment::class, 'c')
                            ->join('c.reactions', 'reaction', null, null, 'reaction.id')
                            ->where('c.id IN (:ids)')
                            ->setParameter('ids', $ids)
                            ->getQuery()
                            ->getResult();
        };*/

        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($onlyVisible) {
            $qb->addSelect('reaction');
            if ($onlyVisible === true) {
                $qb->leftJoin('c.reactions', 'reaction', Kdyby\Doctrine\Dql\Join::WITH, 'reaction.isHidden = 0', 'reaction.id');
            } else {
                $qb->leftJoin('c.reactions', 'reaction', null, null, 'reaction.id');
            }
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