<?php

namespace  Users;

use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Doctrine\EntityManager;
use Nette\DI\CompilerExtension;
use Nette\Http\Session;
use Nette\Security\IUserStorage;
use Users\Authentication\UserStorage;

class UsersExtension extends CompilerExtension implements IEntityProvider
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);
    }

    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();

        $userStorage = $cb->getDefinition($cb->getByType(IUserStorage::class));
        $userStorage->setClass(
            UserStorage::class,
            ['@'.$cb->getByType(Session::class), '@'.$cb->getByType(EntityManager::class)]
        );
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    public function getEntityMappings()
    {
        return ['Users' => __DIR__ . '/..'];
    }

}