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


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    public function addLogType(LogType $logType)
    {
        $this->em->persist($logType);
        $this->logType = $logType;
        return $this;
    }


    public function addEvent($eventLogName)
    {
        if (!isset($this->logType)) {
            throw new LogTypeNotFoundException(sprintf('Did you set a %s object? You have to use method addLogType() first', LogType::class));
        }
        
        $eventLog = new EventLog($eventLogName, $this->logType);
        $this->em->persist($eventLog);

        return $this;
    }
}