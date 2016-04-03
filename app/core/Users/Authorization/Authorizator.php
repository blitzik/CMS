<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 23.3.2016
 * Time: 9:15
 */

namespace Users\Authorization;

use Nette\Security\IAuthorizator;
use Kdyby\Doctrine\EntityManager;
use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\IResource;
use Nette\Caching\IStorage;
use Nette\Utils\Validators;
use Nette\Caching\Cache;
use Nette\Object;
use Users\User;

class Authorizator extends Object implements IAuthorizator
{
    const CACHE_NAMESPACE = 'users.authorization';

    /** @var Cache */
    private $cache;

    /** @var Permission */
    private $acl;

    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager, IStorage $storage)
    {
        $this->em = $entityManager;
        $this->cache = new Cache($storage, self::CACHE_NAMESPACE);
        $this->acl = $this->loadACL();
    }

    
    /**
     * @param IAuthorizationDefinition $authorizationDefinition
     */
    public function addDefinition(IAuthorizationDefinition $authorizationDefinition)
    {
        $authorizationDefinition->createDefinitions($this->acl);
    }


    /**
     * @param string $role
     * @param IResource|string $resource
     * @param $privilege
     * @return bool
     */
    public function isAllowed($role, $resource, $privilege)
    {
        $roles = [];
        if ($role instanceof User) {
            $roles = $role->getRoles();

        } elseif ($role instanceof \Nette\Security\User) {
            $userIdentity = $role->getIdentity();
            if ($userIdentity !== null) {
                $roles = $role->getIdentity()->getRoles();
            }

        } elseif ($role instanceof Role) {
            $roles[] = $role->getName();

        } elseif (Validators::is($role, 'unicode:1..')) {
            $roles[] = $role;
        } else {
            return false;
        }

        try {
            foreach ($roles as $role) {
                if ($this->acl->isAllowed($role, $resource, $privilege) === true) {
                    return true;
                }
            }

            return false;

        } catch (InvalidStateException $e) {
            return false; // role does not exists
        }
    }


    private function loadACL()
    {
        return $this->cache->load('acl', function () {
            return $this->createACL();
        });
    }


    private function createACL()
    {
        $acl = new Permission();

        $this->loadRoles($acl);
        $this->loadResources($acl);
        $this->loadPermissions($acl);

        return $acl;
    }


    private function loadRoles(Permission $acl)
    {
        $roles = $this->em->createQuery(
            'SELECT r, parent FROM ' . Role::class . ' r
             LEFT JOIN r.parent parent
             ORDER BY r.parent ASC'
        )->execute();

        /** @var Role $role */
        foreach ($roles as $role) {
            $acl->addRole($role->getName(), $role->hasParent() ? $role->getParentName() : null);
        }

        $acl->addRole(Role::GOD);
    }


    private function loadResources(Permission $acl)
    {
        $resources = $this->em->createQuery(
            'SELECT r FROM ' . Resource::class . ' r'
        )->execute();

        /** @var Resource $resource */
        foreach ($resources as $resource) {
            $acl->addResource($resource->getName());
        }
    }


    private function loadPermissions(Permission $acl)
    {
        $permissions = $this->em->createQuery(
            'SELECT p, pr FROM ' . \Users\Authorization\Permission::class . ' p
             LEFT JOIN p.privilege pr'
        )->execute();

        /** @var \Users\Authorization\Permission $permission */
        foreach ($permissions as $permission) {
            if ($permission->isAllowed() === true) {
                $acl->allow($permission->getRoleName(), $permission->getResourceName(), $permission->getPrivilegeName());
            } else {
                $acl->deny($permission->getRoleName(), $permission->getResourceName(), $permission->getPrivilegeName());
            }
        }

        $acl->allow(Role::GOD, IAuthorizator::ALL, IAuthorizator::ALL);
    }

}