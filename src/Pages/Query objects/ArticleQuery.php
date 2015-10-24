<?php

namespace Pages\Query;

use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;
use Pages\Article;

class ArticleQuery extends QueryObject
{
    /** @var array  */
    private $select = [];

    /** @var array  */
    private $filter = [];

    public function forOverview()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->select('partial a.{id, title, intro, publishedAt}, t');
        };

        return $this;
    }

    public function onlyPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('a.publishedAt <= CURRENT_TIMESTAMP() AND a.isPublished = true');
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
        $qb->leftJoin('a.tags', 't')
           ->orderBy('a.publishedAt', 'DESC');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

    private function createBasicDQL(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->select('a, t')
           ->from(Article::class, 'a');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}