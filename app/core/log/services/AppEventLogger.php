<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Log\Services;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Log\Log;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Nette\Object;
use Log\EventLog;
use Log\LogType;
use Users\User;

class AppEventLogger extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Cache */
    private $cache;

    /** @var EntityRepository */
    private $eventLogRepository;

    /** @var EntityRepository */
    private $eventTypeRepository;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, 'eventLogging');

        $this->eventLogRepository = $entityManager->getRepository(EventLog::class);
        $this->eventTypeRepository = $entityManager->getRepository(LogType::class);
    }


    /**
     * @param string $message
     * @param string $event
     * @param int|null $userID
     * @throws \Exception
     */
    public function saveLog($message, $event, $userID = null)
    {
        $eventLog = $this->getEventLog($event);
        if ($eventLog === null) {
            return; // todo monolog log
        }

        try {
            $user = null;
            if (isset($userID)) {
                $user = $this->em->getReference(User::class, $userID);
            }

            $newLog = new Log($message, $eventLog, $user);
            $this->em->persist($newLog)->flush();

        } catch (ForeignKeyConstraintViolationException $e) {
            // todo if user doesn't exist
            $this->cache->remove(self::getEventLogCacheKey($event));
        }
    }


    /**
     * @param $event
     * @return EventLog|null Returns null if there is no EventLog with given name
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    private function getEventLog($event)
    {
        $eventLogCacheKey = self::getEventLogCacheKey($event);

        $eventLogID = $this->cache->load($eventLogCacheKey);
        if ($eventLogID !== null) {
            return $this->em->getReference(EventLog::class, $eventLogID);
        }

        /** @var EventLog $eventLog */
        $eventLog = $this->eventLogRepository->findOneBy(['name' => $event]);
        if ($eventLog === null) {
            return null;
        }

        $this->cache->save($eventLogCacheKey, $eventLog->getId());

        return $eventLog;
    }


    /**
     * @param string $event
     * @return string
     */
    public static function getEventLogCacheKey($event)
    {
        return sprintf('eventLog_%s', $event);
    }

}