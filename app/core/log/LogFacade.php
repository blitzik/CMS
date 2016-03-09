<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Log\Facades;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Log\Log;
use Log\Query\LogQuery;
use Nette\Object;

class LogFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $logRepository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->logRepository = $em->getRepository(Log::class);
    }


    /**
     * @param LogQuery $logQuery
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchLogs(LogQuery $logQuery)
    {
        return $this->logRepository->fetch($logQuery);
    }

}