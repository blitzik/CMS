<?php

namespace Pages\Facades;

use App\Exceptions\LogicExceptions\DateTimeFormatException;
use Pages\Exceptions\Runtime\PagePublicationException;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Pages\Exceptions\Runtime\UrlAlreadyExistsException;
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
use Tags\Tag;
use Url\Url;

class PageFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Logger */
    private $logger;

    /** @var  EntityRepository */
    private $pageRepository;

    /** @var  Cache */
    private $cache;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        Logger $logger
    )
    {
        $this->em = $entityManager;
        $this->logger = $logger->channel('pages');
        $this->cache = new Cache($storage, 'articles');

        $this->pageRepository = $this->em->getRepository(Page::class);
    }


    /**
     * @param Page $page
     * @param array $tags
     * @return Page
     * @throws PageTitleAlreadyExistsException
     * @throws UrlAlreadyExistsException
     * @throws PagePublicationException
     * @throws DateTimeFormatException
     */
    public function save(Page $page, $values, array $tags)
    {
        $values['tags'] = $tags; // values from Form [key => tagId]

        if ($values['time'] == null and $values['isPublished'] == true) {
            throw new PagePublicationException;
        }

        try {
            $this->em->beginTransaction();

            if ($page->getId() === null) {
                $page = $this->createNewPage($page, $values);
            } else {
                $page = $this->updatePage($page, $values);
            }

            $this->em->flush();
            $this->em->commit();

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('Page saving error:' . $e->getMessage());
        }

        return $page;
    }


    /**
     * @param Page $page
     * @param $values
     * @return Page
     * @throws \Exception
     * @throws PageTitleAlreadyExistsException
     * @throws PagePublicationException
     * @throws UrlAlreadyExistsException
     * @throws DateTimeFormatException
     */
    private function createNewPage(Page $page, $values)
    {
        $page->setPublishedAt($values['time']);
        $page->setArticleVisibility($values['isPublished']);

        /** @var Page $page */
        $page = $this->em->safePersist($page);
        if ($page === false) {
            throw new PageTitleAlreadyExistsException;
        }

        $pageUrl = $this->establishPageUrl($page);
        $pageUrl = $this->em->safePersist($pageUrl);
        if ($pageUrl === false) {
            throw new UrlAlreadyExistsException;
        }

        $this->addTags2Page($page, $values['tags']);

        $this->em->persist($page);

        return $page;
    }


    /**
     * @param Page $page
     * @param $values
     * @return Page
     * @throws DateTimeFormatException
     */
    private function updatePage(Page $page, $values)
    {
        $page->setTitle($values['title']);
        $page->setIntro($values['intro']);
        $page->setText($values['text']);
        $page->setPublishedAt($values['time']);
        $page->setArticleVisibility($values['isPublished']);

        $page->clearTags();

        $this->addTags2Page($page, $values['tags']);

        $this->em->persist($page);

        return $page;
    }


    private function addTags2Page(Page $page, array $tags)
    {
        foreach ($tags as $tagId) {
            /** @var Tag $tag */
            $tag = $this->em->getReference(Tag::class, $tagId);
            $page->addTag($tag);
        }
    }


    /**
     * @param Page $page
     * @return Url
     */
    private function establishPageUrl(Page $page)
    {
        $url = new Url;
        $url->setUrlPath(Strings::webalize($page->title));
        $url->setDestination(Page::PRESENTER, Page::PRESENTER_ACTION);
        $url->setInternalId($page->getId());

        return $url;
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
     * @param Page $page
     * @throws DBALException
     */
    public function removePage(Page $page)
    {
        try {
            $this->em->beginTransaction();

            $url_path = Strings::webalize($page->title);

            /** @var Url $url */
            $url = $this->em->createQuery(
                'SELECT u FROM ' . Url::class . ' u
                 WHERE u.urlPath = :url_path'
            )->setParameter('url_path', $url_path)
                ->getOneOrNullResult();

            if ($url !== null) {
                $this->cache->clean([Cache::TAGS => $url->getCacheKey()]);
                $this->em->remove($url);
                $this->em->remove($page);
                $this->em->flush();
            }

            $this->em->commit();

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

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