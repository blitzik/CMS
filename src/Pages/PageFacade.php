<?php

namespace Pages\Facades;

use Pages\Exceptions\Logic\DateTimeFormatException;
use Pages\Exceptions\Runtime\PagePublicationTimeException;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Pages\Services\PageRemover;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\Strings;
use Pages\Page;
use Pages\Query\PageQuery;
use Pages\Services\PagePersister;
use Url\Url;

class PageFacade extends Object
{
    /** @var  EntityRepository */
    private $pageRepository;

    /** @var PagePersister */
    private $pagePersister;

    /** @var PageRemover */
    private $pageRemover;

    /** @var Logger */
    private $logger;

    /** @var  Cache */
    private $cache;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $entityManager,
        PagePersister $pagePersister,
        PageRemover $pageRemover,
        IStorage $storage,
        Logger $logger
    )
    {
        $this->em = $entityManager;
        $this->pagePersister = $pagePersister;
        $this->pageRemover = $pageRemover;
        $this->logger = $logger->channel('pages');
        $this->cache = new Cache($storage, 'articles');

        $this->pageRepository = $this->em->getRepository(Page::class);
    }


    /**
     * @param array $values
     * @param Page|null $page
     * @return Page
     * @throws PageTitleAlreadyExistsException
     * @throws UrlAlreadyExistsException
     * @throws PagePublicationTimeException
     * @throws DateTimeFormatException
     * @throws \Exception
     */
    public function save(array $values, Page $page = null)
    {
        return $this->pagePersister->save($values, $page);
    }


    /**
     * @param Page $page
     * @throws DBALException
     */
    public function removePage(Page $page)
    {
        $this->pageRemover->remove($page);
    }


    /**
     * @param PageQuery $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchPages(PageQuery $query)
    {
        return $this->pageRepository->fetch($query);
    }


    /**
     * @param $pageID
     * @return Page|null
     */
    public function getPage($pageID)
    {
        return $this->getBasePageDql()
                    ->innerJoin('p.url', 'u')
                    ->addSelect('u')
                    ->where('p.id = :id')
                    ->setParameter('id', $pageID)
                    ->getQuery()
                    ->getOneOrNullResult();
    }


    /**
     * @param $pageID
     * @return array|null
     */
    public function getPageAsArray($pageID)
    {
        $page = $this->getBasePageDql()
            ->where('p.id = :id AND p.isPublished = true AND p.publishedAt <= CURRENT_TIMESTAMP()')
            ->setParameter('id', $pageID)
            ->getQuery()
            ->getArrayResult();

        if (empty($page)) {
            return null;
        }

        return $page[0];
    }


    public function publishPage($id)
    {
        $this->em->createQuery(
            'UPDATE ' . Page::class . ' p SET p.isPublished = true
             WHERE p.id = :id'
        )->execute(['id' => $id]);
    }


    public function hidePage($id)
    {
        $this->em->createQuery(
            'UPDATE ' . Page::class . ' p SET p.isPublished = false
             WHERE p.id = :id'
        )->execute(['id' => $id]);
    }


    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    private function getBasePageDql()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p, t')
            ->from(Page::class, 'p')
            ->leftJoin('p.tags', 't');

        return $qb;
    }
}