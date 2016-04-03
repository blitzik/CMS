<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 03.04.2016
 */

namespace Users\Services;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Users\Authentication\UserAuthenticator;
use Kdyby\Doctrine\EntityManager;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Nette\Object;
use Users\User;

class UserRemover extends Object
{
    public $onSuccessUserRemoval;


    /** @var Cache */
    private $cache;

    /** @var EntityManager */
    private $em;

    public function __construct(
        EntityManager $entityManager,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, UserAuthenticator::CACHE_NAMESPACE);
    }


    /**
     * @param User $user
     * @throws ForeignKeyConstraintViolationException
     */
    public function remove(User $user)
    {
        try {
            $this->cache->remove($user); // will be recreated if an error occur 
            
            $userID = $user->getId();
            $this->em->remove($user);
            $this->em->flush();


            $this->onSuccessUserRemoval($user, $userID);

        } catch (ForeignKeyConstraintViolationException $e) {
            // todo log
            throw $e;
        }
    }
}