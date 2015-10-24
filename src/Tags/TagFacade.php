<?php

namespace Tags\Facades;

use Kdyby\Doctrine\EntityManager;
use Nette\Object;
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
        $tags = $this->em->createQuery(
            'SELECT t FROM ' . Tag::class . ' t
             ORDER BY t.name'
        )->getArrayResult();

        return $tags;
    }
}