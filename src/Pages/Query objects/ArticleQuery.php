<?php

namespace Pages\Query;

use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;
use Pages\Article;
use Tags\Tag;

class ArticleQuery extends QueryObject
{
    /** @var array  */
    private $select = [];

    /** @var array  */
    private $filter = [];

    public function onlyWith(array $fields)
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($fields) {
            if (!empty($fields)) {
                $props = implode(',', $fields);
                $qb->select('partial a.{id, ' .$props. '}');
            }
        };

        return $this;
    }

    public function withTags()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->leftJoin('a.tags', 't');
            $qb->addSelect('t');
        };

        return $this;
    }

    public function forOverview()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->select('partial a.{id, title, intro, publishedAt}');
        };

        return $this;
    }

    public function onlyPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('a.isPublished = true AND a.publishedAt <= CURRENT_TIMESTAMP()');
        };

        return $this;
    }

    public function waitingForBeingPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->resetDQLParts(['join', 'orderBy']);
            $qb->where('a.isPublished = true AND a.publishedAt > CURRENT_TIMESTAMP()');
            $qb->orderBy('a.publishedAt', 'ASC');
        };

        return $this;
    }

    public function notPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('a.isPublished = false');
        };

        return $this;
    }

    public function orderByPublishedAt($order)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($order) {
            $qb->orderBy('a.publishedAt', $order);
        };

        return $this;
    }

    /**
     * @param Queryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateCountQuery(Queryable $repository)
    {
        $qb = $this->createBasicDQL($repository->getEntityManager());
        $qb->select('COUNT(a.id)');

        return $qb;
    }


    /**
     * @param \Kdyby\Persistence\Queryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicDQL($repository->getEntityManager());

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

    private function createBasicDQL(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->select('a')
           ->from(Article::class, 'a');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}