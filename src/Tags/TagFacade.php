<?php

namespace Tags\Facades;

use App\Exceptions\Runtime\TagNameAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Nette\Utils\Arrays;
use Pages\Article;
use Tags\Tag;

class TagFacade extends Object
{
    /** @var EntityManager  */
    private $em;

    /** @var Logger  */
    private $logger;

    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('Tags');
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
     * @param $tagId
     * @return array
     */
    public function getTagAsArray($tagId)
    {
        return $this->em->createQuery(
            'SELECT t FROM ' . Tag::class . ' t WHERE t.id = :id'
        )->setParameter('id', $tagId)->getArrayResult();
    }

    /**
     * @return array
     */
    public function findAllTags($ordered = true)
    {
        $qb = $this->getBasicDql();

        if ($ordered === true) {
            $qb->orderBy('t.name', 'ASC');
        }

        $tags = $qb->getQuery()
                   ->getArrayResult();

        return Arrays::associate($tags, 'id');
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