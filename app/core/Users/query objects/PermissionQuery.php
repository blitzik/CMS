<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 30.03.2016
 */

namespace Users\Query;

use Users\Authorization\Permission;
use Kdyby\Persistence\Queryable;
use Kdyby\Doctrine\QueryObject;
use Kdyby;

class PermissionQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    public function byRole($roleID)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($roleID) {
            $qb->andWhere('p.role = :roleID')->setParameter('roleID', $roleID);
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
        $qb->select('p');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(Permission::class, 'p');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}