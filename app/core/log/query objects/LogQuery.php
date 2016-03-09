<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Log\Query;

use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Persistence\Queryable;
use Log\Log;

class LogQuery extends QueryObject
{
    /** @var array */
    private $select = [];

    /** @var array  */
    private $filter = [];


    public function withEventLog()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->join('l.event', 'e')
               ->addSelect('e');
        };

        return $this;
    }


    public function withLogType()
    {
        $this->select[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->join('l.logType', 't')
               ->addSelect('t');
        };

        return $this;
    }


    public function byLogEvent($logEventID)
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) use ($logEventID) {
            $qb->andWhere('l.event = :logEvent')->setParameter('logEvent', $logEventID);
        };

        return $this;
    }


    public function descendingOrder()
    {
        $this->filter[] = function (Kdyby\Doctrine\QueryBuilder $qb) {
            $qb->orderBy('l.id', 'DESC');
        };

        return $this;
    }


    protected function doCreateCountQuery(Queryable $repository)
    {
        $qb = $this->createBasicQuery($repository->getEntityManager());
        $qb->select('COUNT(l.id)');

        return $qb;
    }


    protected function doCreateQuery(Kdyby\Persistence\Queryable $repository)
    {
        $qb = $this->createBasicQuery($repository->getEntityManager());
        $qb->select('l');

        foreach ($this->select as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }


    private function createBasicQuery(Kdyby\Doctrine\EntityManager $entityManager)
    {
        $qb = $entityManager->createQueryBuilder();
        $qb->from(Log::class, 'l');

        foreach ($this->filter as $modifier) {
            $modifier($qb);
        }

        return $qb;
    }

}