<?php

namespace Tags\Facades;

use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Doctrine\DBAL\DBALException;
use Page\Services\TagPersister;
use Page\Services\TagRemover;
use Kdyby\Doctrine\ResultSet;
use Kdyby\Monolog\Logger;
use Tags\Query\TagQuery;
use Nette\Object;
use Tags\Tag;

class TagFacade extends Object
{
    /** @var EntityManager  */
    private $em;

    /** @var Logger  */
    private $logger;

    /** @var EntityRepository */
    private $tagRepository;

    /** @var TagPersister */
    private $tagPersister;

    /** @var TagRemover */
    private $tagRemover;


    public function __construct(
        EntityManager $entityManager,
        TagPersister $tagPersister,
        TagRemover $tagRemover,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->tagPersister = $tagPersister;
        $this->logger = $logger->channel('Tags');

        $this->tagRepository = $this->em->getRepository(Tag::class);
        $this->tagRemover = $tagRemover;
    }


    /**
     * @param array $values
     * @param Tag $tag
     * @return bool|object|Tag
     * @throws TagNameAlreadyExistsException
     * @throws UrlAlreadyExistsException
     * @throws DBALException
     */
    public function saveTag(array $values, Tag $tag = null)
    {
        return $this->tagPersister->save($values, $tag);
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
     * @param int $tagID
     * @throws \Exception
     */
    public function removeTag($tagID)
    {
        $this->tagRemover->remove($tagID);
    }

}