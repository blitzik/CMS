<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 17:15
 */

namespace Users\Facades;

use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Users\Query\UserQuery;
use Nette\Object;
use Users\User;

class UserFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $userRepository;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }


    /**
     * @param UserQuery $query
     * @return User
     */
    public function fetchUser(UserQuery $query)
    {
        return $this->userRepository->fetchOne($query);
    }


    /**
     * @param UserQuery $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchUsers(UserQuery $query)
    {
        return $this->userRepository->fetch($query);
    }
}