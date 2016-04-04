<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Log\Services;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\Validators;
use Nette\Caching\IStorage;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Http\Request;
use Nette\Object;
use Log\EventLog;
use Users\User;
use Log\Log;

class AppEventLogger extends Object
{
    /** @var Request */
    private $request;

    /** @var Cache */
    private $cache;

    /** @var EntityManager */
    private $em;

    /** @var mixed */
    private $eventsToSkip;

    /** @var Logger */
    private $logger;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        Request $request,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->request = $request;
        $this->cache = new Cache($storage, Log::CACHE_NAMESPACE);
        $this->logger = $logger->channel('event-logger');
    }


    /**
     * @param array $eventsToSkip * or [logType => *] or [logType1 => [event1, event2, ...], logType2 => ...]
     */
    public function addEventsToSkip($eventsToSkip)
    {
        if ($eventsToSkip === '*') {
            $this->eventsToSkip = $eventsToSkip;
        } elseif (is_array($eventsToSkip)) {
            foreach ($eventsToSkip as $type => $events) {
                if (!Validators::is($type, 'unicode:1..')) {
                    $this->logger->addWarning(sprintf('The keys of array $eventsToSkip must be non-empty strings. "%s" given', gettype($type)));
                    continue;
                }

                if ($events === '*') {
                    $this->eventsToSkip[$type] = $events;
                } elseif (is_array($events)) {
                    foreach ($events as $event) {
                        if (Validators::is($event, 'unicode:1..')) {
                            $this->eventsToSkip[$type][$event] = $event;
                        } else {
                            $this->logger->addWarning(sprintf('The values of array $eventsToSkip[%s] must be non-empty string. "%s" given', $type, gettype($event)));
                        }
                    }
                } else {
                    $this->logger->addWarning(sprintf('The values of array $eventsToSkip must be an ARRAY or * (a star). "%s" given', gettype($events)));
                }
            }
        } else {
            $this->logger->addWarning(sprintf('Argument $eventsToSkip must be an ARRAY or * (a star). "%s" given', gettype($eventsToSkip)));
        }
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

        if (!$this->isEventLoggable($eventLog)) {
            return;
        }

        try {
            $user = null;
            if (isset($userID)) {
                $user = $this->em->getReference(User::class, $userID);
            }

            $newLog = new Log($message, $this->em->getReference(EventLog::class, $eventLog->getId()), $this->request->getRemoteAddress(), $user);
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
        $eventLog = $this->cache->load(self::getEventLogCacheKey($event), function () use ($event) {
            return $this->em->createQuery(
                'SELECT el, lt FROM ' . EventLog::class . ' el
                 JOIN el.logType lt
                 WHERE el.name = :name'
            )->setParameter('name', $event)
             ->getOneOrNullResult();
        });

        return $eventLog;
    }


    /**
     * @param EventLog $eventLog
     * @return bool
     */
    private function isEventLoggable(EventLog $eventLog)
    {
        $eventType = $eventLog->getLogTypeName();
        $eventName = $eventLog->getName();

        if ($this->eventsToSkip === '*' or
            (isset($this->eventsToSkip[$eventType]) and $this->eventsToSkip[$eventType] === '*') or
            (isset($this->eventsToSkip[$eventType][$eventName]) and $this->eventsToSkip[$eventType][$eventName] === $eventName)) {
            return false;
        }

        return true;
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