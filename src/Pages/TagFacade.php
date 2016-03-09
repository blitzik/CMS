<?php

namespace Tags\Facades;

use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\ResultSet;
use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Pages\Page;
use Tags\Query\TagQuery;
use Tags\Tag;

class TagFacade extends Object
{
    public $onSuccessTagCreation;
    public $onSuccessTagEditing;
    public $onSuccessTagRemoval;

    /** @var EntityManager  */
    private $em;

    /** @var Logger  */
    private $logger;

    /** @var EntityRepository */
    private $tagRepository;

    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('Tags');

        $this->tagRepository = $this->em->getRepository(Tag::class);
    }

    /**
     * @param Tag $tag
     * @return bool|object|Tag
     * @throws TagNameAlreadyExistsException
     * @throws DBALException
     */
    public function saveTag(Tag $tag)
    {
        try {
            if ($tag->getId() === null) {
                $tag = $this->em->safePersist($tag);
                if ($tag === false) {
                    throw new TagNameAlreadyExistsException;
                }
                $this->onSuccessTagCreation($tag);
            } else {
                $this->em->persist($tag)->flush();
                $this->onSuccessTagEditing($tag);
            }

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('tag error'); // todo

            throw $e;
        }

        return $tag;
    }


    /**
     * @param TagQuery $tagQuery
     * @return Tag|null
     */
    public function fetchTag(TagQuery $tagQuery)
    {
        return $this->tagRepository->fetchOne($tagQuery);
    }


    /**
     * @param TagQuery $tagQuery
     * @return array|ResultSet
     */
    public function fetchTags(TagQuery $tagQuery)
    {
        return $this->tagRepository->fetch($tagQuery);
    }


    /**
     * @param int $id
     * @return Tag|null
     */
    public function find($id)
    {
        return $this->tagRepository->find($id);
    }


    /**
     * @param Tag $tag
     * @throws \Exception
     */
    public function removeTag(Tag $tag)
    {
        $id = $tag->getId();
        $this->em->remove($tag)->flush();

        $this->onSuccessTagRemoval($tag, $id);
    }

}