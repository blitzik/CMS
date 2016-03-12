<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.03.2016
 */

namespace Page\Services;

use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Kdyby\Doctrine\EntityManager;
use Url\Generators\UrlGenerator;
use Url\Facades\UrlFacade;
use Nette\Object;
use Tags\Tag;

class TagPersister extends Object
{
    public $onSuccessTagCreation;
    public $onSuccessTagEditing;

    /** @var EntityManager  */
    private $em;

    /** @var UrlFacade */
    private $urlFacade;


    public function __construct(
        EntityManager $em,
        UrlFacade $urlFacade
    ) {

        $this->em = $em;
        $this->urlFacade = $urlFacade;
    }


    /**
     * @param array $values
     * @param Tag|null $tag
     * @return Tag
     * @throws TagNameAlreadyExistsException
     * @throws UrlAlreadyExistsException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function save(array $values, Tag $tag = null)
    {
        try {
            if ($tag !== null and $tag->getId() !== null) {
                $tag = $this->update($values, $tag);
                $this->onSuccessTagEditing($tag);

            } else {
                $tag = $this->create($values, $tag);
                $this->onSuccessTagCreation($tag);
            }

        } catch (TagNameAlreadyExistsException $tge) {
            $this->closeEntityManager();
            throw $tge;

        } catch (UrlAlreadyExistsException $ue) {
            $this->closeEntityManager();
            throw $ue;

        } catch (\Exception $e) {
            $this->closeEntityManager();
            throw $e;
        }

        return $tag;
    }


    /**
     * @param array $values
     * @param Tag|null $tag
     * @return Tag
     * @throws TagNameAlreadyExistsException
     * @throws UrlAlreadyExistsException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    private function create(array $values, Tag $tag = null)
    {
        $this->em->beginTransaction();

        if ($tag === null) {
            $tag = new Tag($values['name'], $values['color']);
        }

        $this->fillTag($values, $tag);

        /** @var Tag $tag */
        $tag = $this->em->safePersist($tag);
        if ($tag === false) {
            throw new TagNameAlreadyExistsException;
        }

        $this->createUrl($tag);

        $this->em->commit();

        return $tag;
    }


    /**
     * @param array $values
     * @param Tag $tag
     * @return Tag
     * @throws \Exception
     */
    private function update(array $values, Tag $tag)
    {
        $this->em->beginTransaction();

        $this->fillTag($values, $tag);

        $this->em->persist($tag)->flush();
        $this->em->commit();

        return $tag;
    }


    /**
     * @param array $values
     * @param Tag $tag
     */
    private function fillTag(array $values, Tag $tag)
    {
        $tag->setColor($values['color']);
    }


    /**
     * @param Tag $tag
     */
    private function createUrl(Tag $tag)
    {
        $url = UrlGenerator::create(
            sprintf('search/%s', $tag->getName()),
            'Pages:Front:Search',
            'tag',
            $tag->getId()
        );

        $this->urlFacade->saveUrl($url);
    }


    private function closeEntityManager()
    {
        $this->em->rollback();
        $this->em->close();
    }
}