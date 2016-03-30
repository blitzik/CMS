<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 30.03.2016
 */

namespace Users\Services;

use Doctrine\DBAL\DBALException;
use Users\Authorization\Authorizator;
use Users\Authorization\Permission;
use Users\Authorization\Privilege;
use Users\Authorization\Resource;
use Kdyby\Doctrine\EntityManager;
use Users\Authorization\Role;
use Nette\Caching\IStorage;
use Nette\Security\User;
use Nette\Caching\Cache;
use Nette\Object;

class RolePermissionsPersister extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Cache */
    private $cache;


    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        User $user
    ) {
        $this->em = $entityManager;
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

            foreach ($permissionDefinitions as $definition => $isAllowed) {
                $isAllowed = (bool)$isAllowed;

                $x = explode('-', $definition); // eg. 1-3
                $resource = $resources[$x[0]];
                $privilege = $privileges[$x[1]];

                $permission = new Permission($role, $resource, $privilege, $isAllowed);
                $this->em->persist($permission);
            }

            $this->em->flush();
            $this->em->commit();

            $this->cache->remove('acl');

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            // todo log error
            
            throw new $e;
        }
    }
}