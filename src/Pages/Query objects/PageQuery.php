<?php

namespace Pages\Query;

use Doctrine\ORM\Query\Expr\Join;
use Kdyby\Persistence\Queryable;
use Kdyby\Doctrine\QueryObject;
use Comments\Comment;
use Pages\Page;
use Kdyby;

class PageQuery extends QueryObject
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
                $qb->select('partial p.{id, ' .$props. '}');
            }
        };

        return $this;
    }


    public function withTags() // doesn't work with $repository->fetchOne()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->indexBy('p', 'p.id');
        };

        $this->onPostFetch[] = function ($_, Queryable $repository, \Iterator $iterator) {
            $ids = array_keys(iterator_to_array($iterator, true));

            $repository->createQueryBuilder()
                       ->select('PARTIAL page.{id}, tags')
                       ->from(Page::class, 'page')
                       ->leftJoin('page.tags', 'tags', null, null, 'tags.id')
                       ->andWhere('page.id IN (:ids)')
                       ->setParameter('ids', $ids)
                       ->getQuery()
                       ->getResult();
        };

        return $this;
    }


    public function withCommentsCount()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->addSelect('COUNT(c.page) AS commentsCount')
               ->leftJoin(Comment::class, 'c', Join::WITH, 'c.page = p')
               ->groupBy('p.id');
        };

        return $this;
    }


    public function forAdminOverview()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->select('partial p.{id, title, createdAt, publishedAt, isDraft, allowedComments}, locale');
            $qb->join('p.locale', 'locale');
        };

        return $this;
    }


    public function forOverview()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->select('partial p.{id, title, intro, introHtml, publishedAt}, locale');
            $qb->join('p.locale', 'locale');
        };

        return $this;
    }


    public function onlyPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('p.isDraft = false AND p.publishedAt <= CURRENT_TIMESTAMP()');
        };

        return $this;
    }


    public function waitingForBeingPublished()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->resetDQLParts(['join', 'orderBy']);
            $qb->where('p.isDraft = false AND p.publishedAt > CURRENT_TIMESTAMP()');
            $qb->orderBy('p.publishedAt', 'ASC');
        };

        return $this;
    }


    public function onlyDrafts()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->andWhere('p.isDraft = true');
        };

        return $this;
    }


    public function orderByPublishedAt($order)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($order) {
            $qb->orderBy('p.publishedAt', $order);
        };

        return $this;
    }


    public function byPageId($pageId)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($pageId) {
            $qb->andWhere('p.id = :id')->setParameter('id', $pageId);
        };

        return $this;
    }


    /**
     * Result (usually array) of the query will indexed by Pages IDs
     *
     * @return $this
     */
    public function indexedByPageId()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->indexBy('p', 'p.id');
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
        $qb->select('COUNT(p.id)');

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
        $qb->select('p')
           ->from(Page::class, 'p');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}