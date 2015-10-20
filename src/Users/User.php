<?php

namespace Users;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Nette\Utils\Validators;

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


    public function __construct(
        $username,
        $email,
        $plainPassword
    ) {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($plainPassword);
    }

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
}