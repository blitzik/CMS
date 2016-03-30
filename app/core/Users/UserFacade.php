<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 17:15
 */

namespace Users\Facades;

use Doctrine\DBAL\DBALException;
use Users\Services\RolePermissionsPersister;
use Users\Authorization\AccessDefinition;
use Users\Query\AccessDefinitionQuery;
use Kdyby\Doctrine\EntityRepository;
use Users\Authorization\Permission;
use Users\Authorization\Privilege;
use Users\Authorization\Resource;
use Kdyby\Doctrine\EntityManager;
use Users\Query\PermissionQuery;
use Users\Authorization\Role;
use Users\Query\RoleQuery;
use Users\Query\UserQuery;
use Nette\Object;
use Users\User;

class UserFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $accessDefinitionRepository;

    /** @var RolePermissionsPersister */
    private $rolePermissionsPersister;

    /** @var EntityRepository */
    private $permissionRepository;

    /** @var EntityRepository */
    private $userRepository;

    /** @var EntityRepository */
    private $roleRepository;


    public function __construct(
        EntityManager $entityManager,
        RolePermissionsPersister $rolePermissionsPersister
    ) {
        $this->em = $entityManager;
        $this->rolePermissionsPersister = $rolePermissionsPersister;

        $this->userRepository = $entityManager->getRepository(User::class);
        $this->roleRepository = $entityManager->getRepository(Role::class);
        $this->permissionRepository = $entityManager->getRepository(Permission::class);
        $this->accessDefinitionRepository = $entityManager->getRepository(AccessDefinition::class);
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


    /**
     * @param RoleQuery $query
     * @return Role
     */
    public function fetchRole(RoleQuery $query)
    {
        return $this->roleRepository->fetchOne($query);
    }


    /**
     * @param RoleQuery $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchRoles(RoleQuery $query)
    {
        return $this->roleRepository->fetch($query);
    }


    /**
     * @param PermissionQuery $query
     * @return Permission
     */
    public function fetchPermission(PermissionQuery $query)
    {
        return $this->permissionRepository->fetchOne($query);
    }


    /**
     * @param PermissionQuery $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchPermissions(PermissionQuery $query)
    {
        return $this->permissionRepository->fetch($query);
    }


    /**
     * @param AccessDefinition $query
     * @return AccessDefinition
     */
    public function fetchAccessDefinition(AccessDefinitionQuery $query)
    {
        return $this->accessDefinitionRepository->fetchOne($query);
    }


    /**
     * @param AccessDefinition $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchAccessDefinitions(AccessDefinitionQuery $query)
    {
        return $this->accessDefinitionRepository->fetch($query);
    }


    /**
     * @param Role $role
     * @param array $permissionDefinitions
     * @throws DBALException
     */
    public function savePermissionDefinitions(Role $role, array $permissionDefinitions)
    {
        $this->rolePermissionsPersister->save($role, $permissionDefinitions);
    }
}