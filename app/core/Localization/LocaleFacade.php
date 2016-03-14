<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 13.03.2016
 */

namespace Localization\Facades;

use Kdyby\Doctrine\EntityManager;
use Nette\Caching\IStorage;
use Localization\Locale;
use Nette\Caching\Cache;
use Nette\Object;

class LocaleFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Cache */
    private $cache;


    public function __construct(EntityManager $em, IStorage $storage)
    {
        $this->em = $em;
        $this->cache = new Cache($storage, 'localization');
    }


    /**
     * @param string $localeName
     * @return Locale|null
     */
    public function getByName($localeName)
    {
        return $this->em->createQuery(
            'SELECT l FROM ' .Locale::class . ' l
             WHERE l.name = :name'
        )->setParameter('name', $localeName)
         ->getOneOrNullResult();
    }


    public function findAllLocales()
    {
        return $this->cache->load('locales', function () {
            return $this->em->createQuery(
                'SELECT l FROM ' . Locale::class . ' l INDEX BY l.name'
            )->getArrayResult();
        });
    }
}