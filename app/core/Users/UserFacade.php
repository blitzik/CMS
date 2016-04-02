<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 17:15
 */

namespace Users\Facades;

use App\ValidationObjects\ValidationObject;
use Users\Exceptions\Runtime\RoleAlreadyExistsException;
use Kdyby\Doctrine\Mapping\ResultSetMappingBuilder;
use Users\Exceptions\Runtime\RoleMissingException;
use Users\Services\RolePermissionsPersister;
use Users\Authorization\AccessDefinition;
use Users\Query\AccessDefinitionQuery;
use Kdyby\Doctrine\EntityRepository;
use Users\Authorization\Permission;
use Users\Services\UserPersister;
use Users\Services\RolePersister;
use Kdyby\Doctrine\EntityManager;
use Doctrine\DBAL\DBALException;
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

    /** @var UserPersister */
    private $userPersister;

    /** @var RolePersister */
    private $rolePersister;


    public function __construct(
        EntityManager $entityManager,
        UserPersister $userPersister,
        RolePersister $rolePersister,
        RolePermissionsPersister $rolePermissionsPersister
    ) {
        $this->em = $entityManager;
        $this->userPersister = $userPersister;
        $this->rolePersister = $rolePersister;
        $this->rolePermissionsPersister = $rolePermissionsPersister;

        $this->userRepository = $entityManager->getRepository(User::class);
        $this->roleRepository = $entityManager->getRepository(Role::class);
        $this->permissionRepository = $entityManager->getRepository(Permission::class);
        $this->accessDefinitionRepository = $entityManager->getRepository(AccessDefinition::class);
    }


    /**
     * @param array $values
     * @param User|null $user
     * @return ValidationObject
     */
    public function saveUser(array $values, User $user = null)
    {
        return $this->userPersister->save($values, $user);
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


    /**
     * @param array $values
     * @return Role
     * @throws RoleAlreadyExistsException
     * @throws RoleMissingException
     */
    public function createRole(array $values)
    {
        return $this->rolePersister->createRole($values);
    }


    /**
     * @return Role[]
     */
    public function findRolesThatAreNotParents()
    {
        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addEntityResult(Role::class, 'r');

        $rsm->addFieldResult('r', 'id', 'id');
        $rsm->addFieldResult('r', 'name', 'name');

        $rsm->addJoinedEntityResult(Role::class, 'p', 'r', 'parent');
        $rsm->addFieldResult('p', 'parent_id', 'id');
        $rsm->addFieldResult('p', 'parent_name', 'name');

        $nativeQuery = $this->em->createNativeQuery('
            SELECT r.id, r.name, p.id AS parent_id, p.name AS parent_name
            FROM role r
            LEFT JOIN role p ON (p.id = r.parent_id)
            WHERE r.id NOT IN(
                SELECT r2.parent_id FROM role r2 WHERE r2.parent_id IS NOT NULL
            )
        ', $rsm);

        return $nativeQuery->getResult();
    }
}