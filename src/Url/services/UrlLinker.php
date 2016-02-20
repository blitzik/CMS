<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 20.02.2016
 */

namespace Url\Services;

use Url\Exceptions\Runtime\UrlNotPersistedException;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Nette\Object;
use Url\Router;
use Url\Url;

class UrlLinker extends Object
{
    /** @var Cache */
    private $cache;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, Router::ROUTE_NAMESPACE);
    }


    /**
     * @param Url $oldUrl
     * @param Url $newUrl
     * @return void
     */
    public function linkUrls(Url $oldUrl, Url $newUrl)
    {
        if ($oldUrl->getId() === null or $newUrl->getId() === null) {
            throw new UrlNotPersistedException;
        }

        $alreadyRedirectedUrls = $this->findByActualUrl($oldUrl->getId());

        /** @var Url $url */
        foreach ($alreadyRedirectedUrls as $url) {
            $url->setRedirectTo($newUrl);
            $this->em->persist($url);
            $this->cache->clean([Cache::TAGS => [$url->getCacheKey()]]);
        }

        $oldUrl->setRedirectTo($newUrl);
        $this->em->persist($oldUrl);
        $this->cache->clean([Cache::TAGS => [$oldUrl->getCacheKey()]]);
    }


    /**
     * @param int $actualUrlID
     * @return array
     */
    private function findByActualUrl($actualUrlID)
    {
        return $this->em->createQuery(
                   'SELECT u FROM ' .Url::class. ' u
                    WHERE u.actualUrlToRedirect = :urlToRedirect'
               )->setParameter('urlToRedirect', $actualUrlID)
                ->getResult();
    }




}