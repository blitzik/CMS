<?php

namespace Options\Facades;

use Options\Services\OptionsPersister;
use Kdyby\Doctrine\EntityManager;
use Doctrine\DBAL\DBALException;
use Nette\Caching\IStorage;
use Nette\Utils\ArrayHash;
use Nette\Caching\Cache;
use Nette\Utils\Arrays;
use Options\Option;
use Nette\Object;

class OptionFacade extends Object
{
    /**
     * @var OptionsPersister
     */
    private $optionsPersister;

    /** @var  Cache */
    private $cache;

    /** @var EntityManager  */
    private $em;


    public function __construct(
        OptionsPersister $optionsPersister,
        EntityManager $entityManager,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->optionsPersister = $optionsPersister;
        $this->cache = new Cache($storage, Option::CACHE_NAMESPACE);
    }


    /**
     * @param array $options
     * @throws DBALException
     */
    public function saveOptions(array $options)
    {
        $this->optionsPersister->save($options);
    }


    /**
     * @return ArrayHash|null
     */
    public function loadOptions()
    {
        $options = $this->cache->load(Option::getCacheKey(), function () use (& $dependencies) {
            $options = $this->em->createQuery(
                'SELECT o FROM ' . Option::class . ' o'
            )->getArrayResult();
            if (empty($options)) {
                return null;
            }

            $options = ArrayHash::from(Arrays::associate($options, 'name=value'));
            $dependencies = [Cache::TAGS => Option::getCacheKey()];
            return $options;
        });

        return $options;
    }
}