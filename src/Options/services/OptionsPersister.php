<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 05.04.2016
 */

namespace Options\Services;

use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching\IStorage;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Options\Option;
use Nette\Object;

class OptionsPersister extends Object
{
    public $onSuccessOptionsSaving;
    
    /** @var Logger  */
    private $logger;

    /** @var Cache */
    private $cache;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, Option::CACHE_NAMESPACE);
        $this->logger = $logger->channel('options');
    }

    
    public function save(array $values)
    {
        $options = $this->prepareOptions($this->findOptions());
        foreach ((array)$values as $key => $value) {
            $options[$key]->setValue($value == '' ? null : $value);
            $this->em->persist($options[$key]);
        }

        try {
            $this->em->flush();
            $this->cache->remove(Option::getCacheKey());
            
            $this->onSuccessOptionsSaving();

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError(sprintf('Save Options error: %s | error message: %s', date('Y-m-d H:i:s'), $e->getMessage()));

            throw $e;
        }

    }


    private function prepareOptions(array $options)
    {
        $result = [];
        /** @var Option $option */
        foreach ($options as $option) {
            $result[$option->getName()] = $option;
        }

        return $result;
    }


    private function findOptions()
    {
        return $this->em->createQuery(
            'SELECT o FROM ' . Option::class . ' o'
        )->getResult();
    }
}