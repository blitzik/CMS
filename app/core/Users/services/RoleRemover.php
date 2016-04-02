<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 02.04.2016
 */

namespace Users\Services;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Users\Authorization\Role;

class RoleRemover extends Object
{
    public $onSuccessRoleRemoval;
    
    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @param Role $role
     * @throws ForeignKeyConstraintViolationException
     */
    public function remove(Role $role)
    {
        try {
            $roleID = $role->getId();
            $this->em->remove($role);
            $this->em->flush();

            $this->onSuccessRoleRemoval($role, $roleID);
            
        } catch (ForeignKeyConstraintViolationException $e) {
            throw $e;
        }
    }
}