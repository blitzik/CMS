<?php

namespace Images\Query;

use Kdyby\Doctrine\QueryObject;
use Images\Image;
use Kdyby;
use Kdyby\Persistence\Queryable;

class ImageQuery extends QueryObject
{
    /** @var  array */
    private $select = [];
    private $filter = [];

    /**
     * @param Queryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateCountQuery(Queryable $repository)
    {
        $qb = $this->getBasicDQL($repository->getEntityManager());
        $qb->select('COUNT(i.id)');

        return $qb;
    }


    /**
     * @param \Kdyby\Persistence\Queryable $repository
     * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
     */
    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->getBasicDQL($repository->getEntityManager());
        $qb->select('i');
        $qb->addOrderBy('i.uploadedAt', 'DESC');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

    private function getBasicDQL(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(Image::class, 'i');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}