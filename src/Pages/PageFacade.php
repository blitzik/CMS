<?php

namespace Pages\Facades;

use Pages\Exceptions\Runtime\PageIntroHtmlLengthException;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Pages\Exceptions\Runtime\PagePublicationTimeException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Kdyby\Doctrine\Mapping\ResultSetMappingBuilder;
use Pages\Exceptions\Logic\DateTimeFormatException;
use Kdyby\Doctrine\EntityRepository;
use Pages\Services\PagePersister;
use Kdyby\Doctrine\EntityManager;
use Doctrine\DBAL\DBALException;
use Pages\Services\PageRemover;
use Nette\Caching\IStorage;
use Pages\Query\PageQuery;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Object;
use Pages\Page;

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
     * @throws PageIntroHtmlLengthException
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
     * @param PageQuery $pageQuery
     * @return Page|null
     */
    public function fetchPage(PageQuery $pageQuery)
    {
        return $this->pageRepository->fetchOne($pageQuery);
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


    public function publishPage($id)
    {
        $this->em->createQuery(
            'UPDATE ' . Page::class . ' p SET p.is_draft = false
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
           ->leftJoin('p.tags', 't', null, null, 't.id');

        return $qb;
    }


    /**
     * Method used for search functionality
     *
     * @param array $tagsIDs
     * @return array
     */
    public function searchByTags(array $tagsIDs)
    {
        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addEntityResult(Page::class, 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'title', 'title');
        $rsm->addFieldResult('p', 'intro', 'intro');
        //$rsm->addFieldResult('p', 'text', 'text');

        $rsm->addMetaResult('p', 'author', 'author');
        $rsm->addMetaResult('p', 'url', 'url');

        $rsm->addFieldResult('p', 'created_at', 'createdAt');
        $rsm->addFieldResult('p', 'is_draft', 'isDraft');
        $rsm->addFieldResult('p', 'published_at', 'publishedAt');
        $rsm->addFieldResult('p', 'allowed_comments', 'allowedComments');

        $rsm->addScalarResult('commentsCount', 'commentsCount', 'integer');

        $rsm->addIndexByColumn('p', 'id');

        $nativeQuery = $this->em->createNativeQuery(
            'SELECT p.id, p.title, p.intro, p.author, p.url,
                    p.created_at, p.is_draft, p.published_at,
                    p.allowed_comments, COUNT(c.page) AS commentsCount
             FROM (
                SELECT pts.page_id, pts.tag_id
                FROM page_tag pts
                WHERE pts.tag_id IN (:ids)
                GROUP BY pts.page_id
             ) AS pt
             JOIN page p ON (p.id = pt.page_id AND p.is_draft = 0 AND p.published_at <= NOW())
             LEFT JOIN comment c ON (c.page = pt.page_id)
             GROUP BY pt.page_id',
            $rsm
        )->setParameter('ids', array_unique($tagsIDs));

        $pages = $nativeQuery->getResult();

        $this->em->createQuery(
            'SELECT PARTIAL page.{id}, tags FROM ' . Page::class . ' page
             LEFT JOIN page.tags tags
             WHERE page.id IN (:ids)'
        )->setParameter('ids', array_keys($pages))
         ->execute();

        return $pages;
    }
}