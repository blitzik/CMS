<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 26.02.2016
 */

namespace Tags\Query;

use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;
use Tags\Tag;

class TagQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    public function byTagId($tagId)
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($tagId) {
            $qb->andWhere('t.id = :id')
               ->setParameter('id', $tagId);
        };

        return $this;
    }


    public function byTagName($tagName)
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($tagName) {
            $qb->andWhere('t.name = :name')
               ->setParameter('name', $tagName);
        };

        return $this;
    }


    public function indexedByTagId()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->indexBy('t', 't.id');
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
        $qb->select('t');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(Tag::class, 't');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}