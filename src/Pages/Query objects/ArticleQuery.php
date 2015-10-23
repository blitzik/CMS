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

    public function onlyPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('a.isPublished = true');
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
        $qb->innerJoin('a.author', 'aa')
           ->leftJoin('a.tags', 't')
           ->orderBy('a.publishedAt', 'DESC');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

    private function createBasicDQL(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->select('a, partial aa.{id, username, firstName, lastName}, t')
           ->from(Article::class, 'a');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}