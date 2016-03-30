<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.03.2016
 */

namespace Users\Authorization;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="access_definition",
 *     uniqueConstraints={@UniqueConstraint(name="resource_privilege", columns={"resource", "privilege"})}
 * )
 */
class AccessDefinition
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", cascade={"persist"})
     * @ORM\JoinColumn(name="resource", referencedColumnName="id", nullable=false)
     * @var Resource
     */
    private $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Privilege", cascade={"persist"})
     * @ORM\JoinColumn(name="privilege", referencedColumnName="id", nullable=false)
     * @var Privilege
     */
    private $privilege;


    public function __construct(
        \Users\Authorization\Resource $resource,
        Privilege $privilege
    ) {
        $this->resource = $resource;
        $this->privilege = $privilege;
    }


    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resource->getId();
    }


    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resource->getName();
    }


    /**
     * @return int
     */
    public function getPrivilegeId()
    {
        return $this->privilege->getId();
    }


    /**
     * @return string
     */
    public function getPrivilegeName()
    {
        return $this->privilege->getName();
    }
}