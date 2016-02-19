<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 19.02.2016
 */

namespace Pages\Services;

use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Pages\Page;
use Url\Facades\UrlFacade;
use Url\Url;

class PageRemover extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var UrlFacade  */
    private $urlFacade;

    /** @var Logger */
    private $logger;

    /** @var Cache */
    private $cache;


    public function __construct(
        EntityManager $entityManager,
        UrlFacade $urlFacade,
        IStorage $storage,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->urlFacade = $urlFacade;
        $this->logger = $logger->channel('pages');

        $this->cache = new Cache($storage, 'pages');
    }


    /**
     * @param Page $page
     * @throws DBALException
     * @throws \Exception
     */
    public function remove(Page $page)
    {
        try {
            $this->em->beginTransaction();

            $this->removePageUrl($page);
            $this->em->remove($page);

            $this->em->flush();
            $this->em->commit();

        } catch (DBALException $e) {
            $this->closeEntityManager();

            $this->logger->addError(sprintf(
                'Article Removal Error: %s | article ID: %d | url ID: %s | exception: %s',
                date('Y-m-d H:i:s'),
                $page->getId(),
                isset($url) ? $url->getId() : 'NO URL',
                $e->getMessage()
            ));

            throw $e;
        }
    }


    /**
     * @param Page $page
     */
    private function removePageUrl(Page $page)
    {
        /** @var Url $url */
        $url = $this->urlFacade->getByPath($page->getUrlPath());

        if ($url !== null) {
            $this->cache->clean([Cache::TAGS => $url->getCacheKey()]);
            $this->em->remove($url);
        }
    }


    private function closeEntityManager()
    {
        $this->em->rollback();
        $this->em->close();
    }
}