<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.01.2016
 */

namespace Navigations;

use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class NavigationReader extends Object
{
    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @param int $navigationId
     * @return array
     */
    public function getEntireNavigation($navigationId)
    {
        $nav = $this->em->createQueryBuilder()
                    ->select('c')
                    ->from(Category::class, 'c')
                    ->where('c.navigation = :navigation')
                    ->orderBy('c.lft', 'ASC')
                    ->setParameter('navigation', $navigationId);

        $q = $nav->getQuery();

        return $q->getArrayResult();
    }
}