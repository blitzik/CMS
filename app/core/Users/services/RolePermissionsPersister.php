<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 30.03.2016
 */

namespace Users\Services;

use Users\Authorization\Authorizator;
use Users\Authorization\Permission;
use Users\Authorization\Privilege;
use Users\Authorization\Resource;
use Kdyby\Doctrine\EntityManager;
use Doctrine\DBAL\DBALException;
use Users\Authorization\Role;
use Nette\Caching\IStorage;
use Nette\Caching\Cache;
use Nette\Object;

class RolePermissionsPersister extends Object
{
    public $onSuccessRolePermissionsEditing;

    /** @var Authorizator */
    private $authorizator;

    /** @var Cache */
    private $cache;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $entityManager,
        Authorizator $authorizator,
        IStorage $storage
    ) {
        $this->em = $entityManager;
        $this->authorizator = $authorizator;
        $this->cache = new Cache($storage, Authorizator::CACHE_NAMESPACE);
    }


    /**
     * @param Role $role
     * @param array $permissionDefinitions
     * @throws DBALException
     * @throws \Exception
     */
    public function save(Role $role, array $permissionDefinitions)
    {
        $resources = $this->em->createQuery(
            'SELECT r FROM ' . Resource::class . ' r INDEX BY r.id'
        )->execute();

        $privileges = $this->em->createQuery(
            'SELECT p FROM ' . Privilege::class . ' p INDEX BY p.id'
        )->execute();

        try {
            $this->em->beginTransaction();
            
            $this->em->createQuery(
                'DELETE ' . Permission::class . ' p
                 WHERE p.role = :role'
            )->execute(['role' => $role->getId()]);

            $parentRole = null;
            if ($role->hasParent()) {
                /** @var Role $parentRole */
                $parentRole = $this->em->find(Role::class, $role->getParentId());
            }

            foreach ($permissionDefinitions as $definition => $isAllowed) {
                $isAllowed = (bool)$isAllowed;

                $x = explode('-', $definition); // eg. 1-3
                /** @var \Users\Authorization\Resource $resource */
                $resource = $resources[$x[0]];
                /** @var Privilege $privilege */
                $privilege = $privileges[$x[1]];

                // check Users\Authorization\Authorizator ACL assembling

                // Role without parent
                // privilege: allowed -> must be in database
                // privilege: denied  -> does NOT have to be in database

                // Role with parent (all depths)
                /*
                  ------------------------------------------------------------
                     parent    |    descendant    |    should be persisted?
                  ------------------------------------------------------------
                     allowed         allowed                  NO
                     allowed         denied                  YES
                     denied          denied                  NO
                     denied          allowed                 YES
                  ------------------------------------------------------------
                    We save records where permission and denial differ

                */
                if ($parentRole !== null) { // has parent
                    if ($this->authorizator->isAllowed($parentRole, $resource->getName(), $privilege->getName()) === $isAllowed) {
                        continue;
                    }
                } else { // doesn't have parent
                    if ($isAllowed === false) {
                        continue;
                    }
                }

                $permission = new Permission($role, $resource, $privilege, $isAllowed);
                $this->em->persist($permission);
            }

            $this->em->flush();
            $this->em->commit();

            $this->cache->remove('acl');
            $this->onSuccessRolePermissionsEditing($role);

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            // todo log error
            
            throw new $e;
        }
    }
}