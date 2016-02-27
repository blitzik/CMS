<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.02.2016
 */

namespace Pages\Services;

use Pages\Exceptions\Runtime\PagePublicationTimeMissingException;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Pages\Exceptions\Runtime\PagePublicationTimeException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Kdyby\Doctrine\EntityManager;
use Url\Facades\UrlFacade;
use Nette\Utils\Strings;
use Nette\Object;
use Pages\Page;
use Tags\Tag;
use Url\Url;

class PagePersister extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var UrlFacade  */
    private $urlFacade;


    public function __construct(
        EntityManager $entityManager,
        UrlFacade $urlFacade
    ) {
        $this->em = $entityManager;
        $this->urlFacade = $urlFacade;
    }


    /**
     * @param array $values
     * @param Page|null $page
     * @return Page
     * @throws PagePublicationTimeException
     * @throws PagePublicationTimeMissingException
     * @throws UrlAlreadyExistsException
     * @throws PageTitleAlreadyExistsException
     * @throws \Exception
     */
    public function save(array $values, Page $page = null)
    {
        foreach ($values as $k => $v) $values[$k] = $v === '' ? null : $v;

        if ($values['publishedAt'] === null and $values['saveAsDraft'] === false) {
            throw new PagePublicationTimeMissingException;
        }

        try {
            $this->em->beginTransaction();

            if ($page !== null and $page->getId() !== null) {
                $this->updatePage($page, $values);
            } else {
                $page = $this->createNewPage($values, $page);
            }

            $this->em->flush();
            $this->em->commit();

        } catch (UrlAlreadyExistsException $u) {
            $this->closeEntityManager();
            throw $u;

        } catch (PageTitleAlreadyExistsException $p) {
            $this->closeEntityManager();
            throw $p;

        } catch (\Exception $e) {
            $this->closeEntityManager();
            throw $e;
        }

        return $page;
    }


    /**
     * @param array $values
     * @param Page|null $page
     * @return Page
     * @throws UrlAlreadyExistsException
     * @throws PageTitleAlreadyExistsException
     */
    private function createNewPage(array $values, Page $page = null)
    {
        $url = $this->establishPageUrl($values['title'], $values['url']);
        $url = $this->urlFacade->saveUrl($url); // still needs to set internalID! (next in code)

        if ($page === null) {
            $page = new Page(
                $values['title'],
                $values['intro'],
                $values['text'],
                $url,
                $values['author']
            );
        }

        $this->fillPageEntity($values, $page);

        /** @var Page $page */
        $page = $this->em->safePersist($page);
        if ($page === false) {
            throw new PageTitleAlreadyExistsException;
        }

        $url->setInternalId($page->getId());
        $this->em->persist($url);

        $this->addTags2Page($values['tags'], $page);
        $this->em->persist($page);

        return $page;
    }


    /**
     * @param Page $page
     * @param array $values
     * @return Page
     * @throws UrlAlreadyExistsException
     * @throws PagePublicationTimeException
     */
    private function updatePage(Page $page, array $values)
    {
        $this->fillPageEntity($values, $page);

        if ($page->getUrlPath() !== Strings::webalize($values['url'])) {
            $newUrl = $this->redirectPageToUrl(
                $values['url'],
                $page
            );
            $page->setUrl($newUrl);
        }

        $page->clearTags();
        $this->addTags2Page($values['tags'], $page);
        $this->em->persist($page);

        return $page;
    }


    /**
     * @param array $values
     * @param Page $page
     * @throws PagePublicationTimeException
     */
    private function fillPageEntity(array $values, Page $page)
    {
        $page->setText($values['text']);
        $page->setTitle($values['title']);
        $page->setIntro($values['intro']);
        $page->setPublishedAt($values['publishedAt']);
        $page->setAllowedComments($values['allowedComments']);

        if ($page->isDraft() and $values['saveAsDraft'] === false) {
            $page->setAsPublished($values['publishedAt']);
        }
    }


    /**
     * @param string $pageTitle
     * @param string|null $urlAddress
     * @return Url
     */
    private function establishPageUrl($pageTitle, $urlAddress = null)
    {
        if ($urlAddress === null) {
            $urlAddress = $pageTitle;
        }

        return $this->createUrl($urlAddress);
    }


    /**
     * @param array $tags
     * @param Page $page
     */
    private function addTags2Page(array $tags, Page $page)
    {
        foreach ($tags as $tagId) {
            /** @var Tag $tag */
            $tag = $this->em->getReference(Tag::class, $tagId);
            $page->addTag($tag);
        }
    }


    /**
     * @param string $urlAddress
     * @return Url
     */
    private function createUrl($urlAddress)
    {
        $url = new Url();
        $url->setDestination(Page::PRESENTER, Page::PRESENTER_ACTION);
        $url->setUrlPath($urlAddress);

        return $url;
    }


    /**
     * @param string $newUrlAddress
     * @param Page $page
     * @return Url
     * @throws UrlAlreadyExistsException
     */
    private function redirectPageToUrl($newUrlAddress, Page $page)
    {
        $newUrlEntity = $this->createUrl($newUrlAddress);
        $newUrlEntity->setInternalId($page->getId());

        // if we first try to save url entity, the current transaction is marked for
        // rollback only if there is unique constraint violation.
        $newUrl = $this->urlFacade->getByPath($newUrlEntity->urlPath);
        if ($newUrl === null) {
            $newUrl = $this->urlFacade->saveUrl($newUrlEntity);
        }

        $oldUrl = $this->urlFacade->find($page->getUrlId());

        $this->urlFacade->linkUrls($oldUrl, $newUrl);

        return $newUrl;
    }


    private function closeEntityManager()
    {
        $this->em->rollback();
        $this->em->close();
    }
}