<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 02.04.2016
 */

namespace Users\Authorization;

use Users\Exceptions\Runtime\ResourceNotFoundException;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * @package Users\Authorization
 */
class AuthorizationRulesGenerator extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var \Users\Authorization\Resource */
    private $resource;

    
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @param \Users\Authorization\Resource $resource
     * @return mixed
     */
    public function addResource(\Users\Authorization\Resource $resource)
    {
        $this->em->persist($resource);
        $this->resource = $resource;
        return $this;
    }


    /**
     * @param Privilege $privilege
     * @param Role $role
     * @return $this
     */
    public function addDefinition(Privilege $privilege, Role $role)
    {
        if (!isset($this->resource)) {
            throw new ResourceNotFoundException('In order to use method addDefinition you have to specify Resource first.');
        }

        $this->addRuleDefinition($this->resource, $privilege, $role);

        return $this;
    }
    


    /**
     * @param \Users\Authorization\Resource $resource
     * @param Privilege $privilege
     * @param Role $role
     */
    public function addRuleDefinition(\Users\Authorization\Resource $resource, Privilege $privilege, Role $role)
    {
        $accessDefinition = new AccessDefinition($resource, $privilege);
        $this->em->persist($accessDefinition);

        $permission = new Permission($role, $resource, $privilege);
        $this->em->persist($permission);
    }
}