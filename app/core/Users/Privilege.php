<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.03.2016
 */

namespace Users\Authorization;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="privilege")
 *
 */
class Privilege
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    protected $name;


    public function __construct(
        $name
    ) {
        $this->setName($name);
    }


    /**
     * @param string $name
     */
    private function setName($name)
    {
        Validators::assert($name, 'unicode:1..255');
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