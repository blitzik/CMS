<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.03.2016
 */

namespace Users\Query;

use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;
use Users\Authorization\Role;

class RoleQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    public function withParent()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->leftJoin('r.parent', 'parent')
               ->addSelect('parent');
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
        $qb->select('r')
           ->orderBy('r.id', 'ASC');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(Role::class, 'r');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}