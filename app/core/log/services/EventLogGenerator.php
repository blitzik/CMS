<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 03.04.2016
 */

namespace Log\Services;

use Log\Exceptions\Runtime\LogTypeNotFoundException;
use Kdyby\Doctrine\EntityManager;
use Log\EventLog;
use Nette\Object;
use Log\LogType;

class EventLogGenerator extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var LogType */
    private $logType;


    public function __construct(
        LogType $logType,
        EntityManager $entityManager
    ) {
        $this->em = $entityManager;

        $this->em->persist($logType);
        $this->logType = $logType;
    }


    /**
     * @param LogType $logType
     * @return $this
     */
    public function addLogType(LogType $logType)
    {
        $this->em->persist($logType);
        $this->logType = $logType;
        return $this;
    }


    /**
     * @param string $eventLogName
     * @return $this
     */
    public function addEvent($eventLogName)
    {
        $eventLog = new EventLog($eventLogName, $this->logType);
        $this->em->persist($eventLog);

        return $this;
    }
}