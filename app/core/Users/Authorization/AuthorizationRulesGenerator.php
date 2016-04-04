<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 02.04.2016
 */

namespace Users\Authorization;

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

    
    public function __construct(
        \Users\Authorization\Resource $resource,
        EntityManager $entityManager
    ) {
        $this->em = $entityManager;

        $this->em->persist($resource);
        $this->resource = $resource;
    }


    /**
     * @param \Users\Authorization\Resource $resource
     * @return $this
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
        $accessDefinition = new AccessDefinition($this->resource, $privilege);
        $this->em->persist($accessDefinition);

        $permission = new Permission($role, $this->resource, $privilege);
        $this->em->persist($permission);

        return $this;
    }
}