<?php

namespace  Users\DI;

use Users\Authorization\IAuthorizationDefinition;
use Kdyby\Translation\DI\ITranslationProvider;
use Kdyby\Doctrine\DI\IEntityProvider;
use App\Extensions\CompilerExtension;
use Users\Authentication\UserStorage;
use App\Fixtures\IFixtureProvider;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\IUserStorage;
use Users\Fixtures\UsersFixture;
use Nette\Http\Session;

class UsersExtension extends CompilerExtension implements IEntityProvider, ITranslationProvider, IFixtureProvider
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/services.neon'), $this->name);
    }


    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();

        $this->setPresenterMapping($cb, ['Users' => 'Users\\*Module\\Presenters\\*Presenter']);

        $userStorage = $cb->getDefinition($cb->getByType(IUserStorage::class));
        $userStorage->setClass(
            UserStorage::class,
            ['@'.$cb->getByType(Session::class), '@'.$cb->getByType(EntityManager::class)]
        );

        $authorizator = $cb->getDefinition($this->prefix('authorizator'));
        foreach ($cb->findByType(IAuthorizationDefinition::class) as $rulesDefinition) {
            $authorizator->addSetup('addDefinition', ['authorizationDefinition' => $rulesDefinition]);
        }
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


    /**
     * Return array of directories, that contain resources for translator.
     *
     * @return string[]
     */
    function getTranslationResources()
    {
        return [
            __DIR__ . '/../lang'
        ];
    }


    /**
     * @return array
     */
    public function getDataFixtures()
    {
        return [
            __DIR__ . '/../fixtures/basic' => [
                UsersFixture::class
            ]
        ];
    }


}