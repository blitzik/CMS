<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:52
 */

namespace Users\Query;

use Kdyby\Persistence\Queryable;
use Kdyby\Doctrine\QueryObject;
use Users\User;
use Kdyby;

class UserQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    public function byId($id)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($id) {
            $qb->andWhere('u.id = :id')->setParameter('id', $id);
        };

        return $this;
    }

    
    public function withRoles()
    {
        $this->onPostFetch[] = function ($_, Queryable $repository, \Iterator $iterator) {
            $ids = array_keys(iterator_to_array($iterator));

            $repository->createQueryBuilder()
                       ->select('PARTIAL u.{id}, role, roleParent')
                       ->from(User::class, 'u')
                       ->leftJoin('u.roles', 'role')
                       ->leftJoin('role.parent', 'roleParent')
                       ->where('u.id IN (:ids)')
                       ->setParameter('ids', $ids)
                       ->getQuery()
                       ->getResult();
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
        $qb->select('u');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(User::class, 'u', 'u.id');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}