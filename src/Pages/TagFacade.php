<?php

namespace Tags\Facades;

use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\ResultSet;
use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
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
            } else {
                $this->em->persist($tag)->flush();
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
     * @param int $id
     * @return Tag|null
     */
    public function find($id)
    {
        return $this->tagRepository->find($id);
    }


    /**
     * @param int $tagId
     */
    public function removeTag($tagId)
    {
        $this->em->createQuery(
            'DELETE ' . Tag::class . ' t WHERE t.id = :id'
        )->execute(['id' => $tagId]);
    }


    /**
     * @param int $id
     * @param string $color Color in HEX format (including #)
     * @return int Number of affected rows
     */
    public function changeColor($id, $color)
    {
        return $this->em->createQuery(
            'UPDATE ' . Tag::class . ' t SET t.color = :color WHERE t.id = :id'
        )->execute(['id' => $id, 'color' => $color]);
    }


    /**
     * @return ResultSet
     */
    public function findAllTags($ordered = true)
    {
        $qb = $this->getBasicDql();
        $qb->indexBy('t', 't.id');

        if ($ordered === true) {
            $qb->orderBy('t.name', 'ASC');
        }

        return new ResultSet($qb->getQuery());
    }


    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    private function getBasicDql()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
           ->from(Tag::class, 't');

        return $qb;
    }
}