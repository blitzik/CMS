<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 31.03.2016
 */

namespace Users\Services;

use Users\Exceptions\Runtime\RoleAlreadyExistsException;
use Users\Exceptions\Runtime\RoleMissingException;
use Kdyby\Doctrine\EntityManager;
use Users\Authorization\Role;
use Nette\Object;

class RolePersister extends Object
{
    public $onSuccessRoleCreation;

    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @param array $values
     * @return Role
     * @throws RoleAlreadyExistsException
     * @throws RoleMissingException
     */
    public function createRole(array $values)
    {
        $parentRole = null;
        if ($values['parent'] !== null) {
            $parentRole = $this->em->find(Role::class, $values['parent']);
            if ($parentRole === null) {
                throw new RoleMissingException;
            }
        }

        $role = new Role($values['name'], $parentRole);
        $role = $this->em->safePersist($role);
        if ($role === false) {
            throw new RoleAlreadyExistsException;
        }

        $this->onSuccessRoleCreation($role);

        return $role;
    }
}