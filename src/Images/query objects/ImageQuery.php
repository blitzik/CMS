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
     * @param $name
     * @return $this
     */
    public function byName($name)
    {
        if ($name === null) {
            return $this;
        }

        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($name) {
            $qb->andWhere('i.originalName LIKE :name')
               ->setParameter('name', $name . '%');
        };

        return $this;
    }


    /**
     * @param $extension
     * @return $this
     */
    public function byExtension($extension)
    {
        if ($extension === null) {
            return $this;
        }

        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($extension) {
            $qb->andWhere('i.extension = :extension')
               ->setParameter('extension', $extension);
        };

        return $this;
    }


    /**
     * @param $maxWidth
     * @return $this
     */
    public function maxWidth($maxWidth)
    {
        if ($maxWidth === null) {
            return $this;
        }

        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($maxWidth) {
            $qb->andWhere('i.width <= :width')
               ->setParameter('width', $maxWidth);
        };

        return $this;
    }


    /**
     * @param $maxHeight
     * @return $this
     */
    public function maxHeight($maxHeight)
    {
        if ($maxHeight === null) {
            return $this;
        }

        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($maxHeight) {
            $qb->andWhere('i.height <= :height')
               ->setParameter('height', $maxHeight);
        };

        return $this;
    }
    

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


    /**
     * @param Kdyby\Doctrine\EntityManager $entityManager
     * @return Kdyby\Doctrine\QueryBuilder
     */
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