<?php

namespace Options\Facades;

use Kdyby\Doctrine\EntityManager;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Options\Option;

class OptionFacade extends Object
{
    /** @var EntityManager  */
    private $em;

    /** @var  Cache */
    private $cache;

    public function __construct(
        EntityManager $entityManager,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, 'blog_options');
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