<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 23.3.2016
 * Time: 14:27
 */

namespace Users\Authorization;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(name="permission")
 *
 */
class Permission
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role", referencedColumnName="id", nullable=false)
     * @var Role
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Resource")
     * @ORM\JoinColumn(name="resource", referencedColumnName="id", nullable=false)
     * @var Resource
     */
    private $resource;

    /**
     * @ORM\Column(name="action", type="string", length=255, nullable=false, unique=false)
     * @var string
     */
    private $action;

    /**
     * @ORM\Column(name="is_allowed", type="boolean", nullable=false, unique=false, options={"default":true})
     * @var bool
     */
    private $isAllowed;


    public function __construct(
        Role $role,
        Resource $resource,
        $action,
        $isAllowed = true
    ) {
        $this->role = $role;
        $this->resource = $resource;
        $this->setAction($action);
        $this->isAllowed = (bool)$isAllowed;
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    /**
     * @param string $action
     */
    private function setAction($action)
    {
        $this->action = $action;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }


    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->isAllowed;
    }


    /*
     * ------------------------
     * ----- ROLE GETTERS -----
     * ------------------------
     */

    /**
     * @return string
     */
    public function getRoleName()
    {
        return $this->role->getName();
    }


    /**
     * @return string
     */
    public function getParentRoleName()
    {
        return $this->role->getParentName();
    }


    /*
     * ----------------------------
     * ----- RESOURCE GETTERS -----
     * ----------------------------
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


}