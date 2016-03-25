<?php

namespace Users;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Users\Authorization\Role;
use Nette\Security\Passwords;
use Nette\Utils\Validators;
use Nette\Utils\Random;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 *
 */
class User
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\Column(name="username", type="string", length=40, nullable=false, unique=true)
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(name="first_name", type="string", length=50, nullable=true, unique=false)
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=50, nullable=true, unique=false)
     * @var string
     */
    protected $lastName;
    
    /**
     * @ORM\Column(name="email", type="string", length=100, nullable=false, unique=true)
     * @var string
     */
    protected $email;
    
    /**
     * @ORM\Column(name="password", type="string", length=60, nullable=false, unique=false, options={"fixed": true})
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(name="token", type="string", length=32, nullable=true, unique=false, options={"fixed": true})
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(name="token_validity", type="date", nullable=true, unique=false)
     * @var \DateTime
     */
    private $tokenValidity;

    /**
     * @ORM\ManyToMany(targetEntity="Users\Authorization\Role", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="user_roles",
     *     joinColumns={@ORM\JoinColumn(name="user", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role", referencedColumnName="id")}
     * )
     * @var Role[]
     */
    private $roles;


    public function __construct(
        $username,
        $email,
        $plainPassword
    ) {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($plainPassword);

        $this->roles = new ArrayCollection();
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        Validators::assert($username, 'unicode:1..40');
        $this->username = $username;
    }


    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        Validators::assert($email, 'email');
        $this->email = $email;
    }


    /**
     * @param string $plainPassword
     */
    public function setPassword($plainPassword)
    {
        $this->password = Passwords::hash($plainPassword);
    }


    public function createToken(\DateTime $validity)
    {
        $this->token = Random::generate(32);
        $this->tokenValidity = $validity;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }


    /**
     * @return \DateTime
     */
    public function getTokenValidity()
    {
        return $this->tokenValidity;
    }


    /**
     * @return string
     */
    public function getName()
    {
        if (!isset($this->firstName) and !isset($this->lastName)) {
            return $this->username;
        }

        return trim($this->firstName . ' ' . $this->lastName);
    }


    /*
     * --------------------
     * ----- ROLES --------
     * --------------------
     */


    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles->add($role);
    }


    /**
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }


    /**
     * @return Role[]
     */
    public function getRolesEntities()
    {
        return $this->roles->toArray();
    }


    /**
     * @return string
     */
    public function getRoles()
    {
        $rolesEntities = $this->roles->toArray();
        $roles = [];
        foreach ($rolesEntities as $role) {
            $roles[] = $role->getName();
        }

        return $roles;
    }
}