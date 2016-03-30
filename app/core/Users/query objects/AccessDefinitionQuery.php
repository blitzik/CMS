<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 30.03.2016
 */

namespace Users\Query;

use Users\Authorization\AccessDefinition;
use Kdyby\Persistence\Queryable;
use Kdyby\Doctrine\QueryObject;
use Kdyby;

class AccessDefinitionQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    protected function doCreateCountQuery(Queryable $repository)
    {
        // todo
    }


    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicQuery($repository->getEntityManager());
        $qb->innerJoin('a.resource', 'r')
           ->innerJoin('a.privilege', 'p')
           ->select('a, r, p')
           ->orderBy('a.resource');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(AccessDefinition::class, 'a');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}