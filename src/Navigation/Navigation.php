<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.01.2016
 */

namespace Navigations;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="navigation")
 *
 */
class Navigation
{
    use Identifier;

    const NAME_LENGTH = 100;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=false, unique=true)
     * @var string
     */
    private $name;


    public function __construct($name)
    {
        $this->setName($name);
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    public function setName($name)
    {
        Validators::assert($name, 'unicode:1..' . self::NAME_LENGTH);
        $this->name = $name;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


}