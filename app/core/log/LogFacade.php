<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Log\Facades;

use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Log\EventLog;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Log\Query\LogQuery;
use Nette\Object;
use Log\LogType;
use Log\Log;

class LogFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $logRepository;

    /** @var Cache */
    private $cache;

    public function __construct(EntityManager $em, IStorage $storage)
    {
        $this->em = $em;
        $this->logRepository = $em->getRepository(Log::class);
        $this->cache = new Cache($storage, Log::CACHE_NAMESPACE);
    }


    /**
     * @param LogQuery $logQuery
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchLogs(LogQuery $logQuery)
    {
        return $this->logRepository->fetch($logQuery);
    }


    public function findTypesNames()
    {
        return $this->cache->load('logTypes', function (& $dependencies) {
            return array_column($this->em->createQuery(
                'SELECT t.id, t.name FROM ' . LogType::class . ' t INDEX BY t.id'
            )->getArrayResult(), 'name', 'id');
        });
    }


    public function findEventsByType($logTypeID)
    {
        return $this->cache->load('logEvents-' . $logTypeID, function (& $dependencies) use ($logTypeID) {
             return array_column($this->em->createQuery(
                'SELECT e.id, e.name FROM ' . EventLog::class . ' e INDEX BY e.id
                 WHERE e.logType = :typeID'
            )->setParameter('typeID', $logTypeID)->getArrayResult(), 'name', 'id');
        });
    }

}