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

    const ACL_CREATE = 'create';
    const ACL_EDIT = 'edit';
    const ACL_REMOVE = 'remove';
    const ACL_VIEW = 'view';

    /**
     * @ORM\ManyToOne(targetEntity="Role", cascade={"persist"})
     * @ORM\JoinColumn(name="role", referencedColumnName="id", nullable=false)
     * @var Role
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", cascade={"persist"})
     * @ORM\JoinColumn(name="resource", referencedColumnName="id", nullable=false)
     * @var Resource
     */
    private $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Privilege")
     * @ORM\JoinColumn(name="privilege", referencedColumnName="id", nullable=false)
     * @var Privilege
     */
    private $privilege;

    /**
     * @ORM\Column(name="is_allowed", type="boolean", nullable=false, unique=false, options={"default":true})
     * @var bool
     */
    private $isAllowed;


    public function __construct(
        Role $role,
        \Users\Authorization\Resource $resource,
        Privilege $privilege,
        $isAllowed = true
    ) {
        $this->role = $role;
        $this->resource = $resource;
        $this->privilege = $privilege;
        $this->isAllowed = (bool)$isAllowed;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


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


    /*
     * -----------------------------
     * ----- PRIVILEGE GETTERS -----
     * -----------------------------
     */


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