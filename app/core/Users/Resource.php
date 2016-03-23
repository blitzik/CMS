<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 23.3.2016
 * Time: 13:39
 */

namespace Users\Authorization;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="resource")
 *
 */
class Resource
{
    use Identifier;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    private $name;


    public function __construct($name)
    {
        $this->setName($name);
    }


    /*
     * -------------------
     * ----- SETTERS -----
     * -------------------
     */


    /**
     * @param string $name
     */
    private function setName($name)
    {
        Validators::assert($name, 'unicode:1..255');
        $this->name = $name;
    }


    /*
     * -------------------
     * ----- GETTERS -----
     * -------------------
     */


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}