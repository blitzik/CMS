<?php

namespace Options\Facades;

use Options\Exceptions\Logic\InvalidArgumentException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Options\Option;

class OptionFacade extends Object
{
    /** @var Logger  */
    private $logger;

    /** @var EntityManager  */
    private $em;

    /** @var  Cache */
    private $cache;

    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, 'blog_options');
        $this->logger = $logger->channel('options');
    }

    public function saveOptions(array $options)
    {
        foreach ($options as $option) {
            if (!$option instanceof Option) {
                throw new InvalidArgumentException('Wrong array member type. Expected ' .Option::class. ' but instead "' . gettype($option) . '" was given');
            }

            $this->em->persist($option);
        }

        try {
            $this->em->flush();
            $this->cache->remove(Option::getCacheKey());
        } catch (DBALException $e) {
            $this->logger->addError(sprintf('Save Options error: %s | error message: %s', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->em->rollback();
            $this->em->close();

            throw $e;
        }
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

    public function findOptions()
    {
        return $this->em->createQuery(
            'SELECT o FROM ' . Option::class . ' o'
        )->getResult();
    }
}