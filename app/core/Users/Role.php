<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 23.3.2016
 * Time: 14:05
 */

namespace Users\Authorization;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Security\IRole;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 *
 */
class Role implements IRole
{
    use Identifier;

    const GUEST = 'guest';
    const USER = 'user';
    const ADMIN = 'admin';
    const GOD = 'god';
    

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, unique=false, onDelete="SET NULL")
     */
    private $parent;


    public function __construct(
        $name,
        Role $parent
    ) {
        $this->setName($name);
        $this->parent = $parent;
    }


    /**
     * @param string $name
     */
    private function setName($name)
    {
        $this->name = $name;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->id;
    }


    /**
     * @return bool
     */
    public function hasParent()
    {
        return $this->parent !== null;
    }


    /*
     * --------------------------
     * ----- PARENT GETTERS -----
     * --------------------------
     */

    
    public function getParentId()
    {
        return $this->parent->getId();
    }
    

    public function getParentName()
    {
        return $this->parent->getName();
    }

}