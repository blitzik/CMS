<?php

namespace Tags\Facades;

use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Pages\Article;
use Tags\Tag;

class TagFacade extends Object
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return array
     */
    public function findAllTags()
    {
        $tags = $this->getBasicDql()
                ->orderBy('t.name', 'ASC')
                ->getQuery()
                ->getArrayResult();

        return $tags;
    }

    private function getBasicDql()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
           ->from(Tag::class, 't');

        return $qb;
    }
}