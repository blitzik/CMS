<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.02.2016
 */

namespace Pages\Services;

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
     * @throws UrlAlreadyExistsException
     * @throws PageTitleAlreadyExistsException
     * @throws \Exception
     */
    public function save(array $values, Page $page = null)
    {
        foreach ($values as $k => $v) $values[$k] = $v === '' ? null : $v;

        if ($values['publishedAt'] === null and $values['isPublished'] === true) {
            throw new PagePublicationTimeException;
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
     */
    private function updatePage(Page $page, array $values)
    {
        $this->fillPageEntity($values, $page);

        $page->clearTags();
        $this->addTags2Page($values['tags'], $page);
        $this->em->persist($page);

        return $page;
    }


    /**
     * @param array $values
     * @param Page $page
     */
    private function fillPageEntity(array $values, Page $page)
    {
        $page->setText($values['text']);
        $page->setTitle($values['title']);
        $page->setIntro($values['intro']);
        $page->setPublishedAt($values['publishedAt']);
        $page->setArticleVisibility($values['isPublished']);
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

        $url = new Url();
        $url->setDestination(Page::PRESENTER, Page::PRESENTER_ACTION);
        $url->setUrlPath(Strings::webalize($urlAddress, '/'));

        return $url;
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


    private function closeEntityManager()
    {
        $this->em->rollback();
        $this->em->close();
    }
}