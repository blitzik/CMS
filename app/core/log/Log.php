<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Log;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="log")
 *
 */
class Log
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\ManyToOne(targetEntity="LogType")
     * @ORM\JoinColumn(name="type", referencedColumnName="id", nullable=false)
     * @var LogType
     */
    private $type;

    /**
     * @ORM\Column(name="message", type="string", length=1000, nullable=false, unique=false)
     * @var string
     */
    private $message;


    public function __construct(
        LogType $type,
        $message
    ) {
        $this->type = $type;
        $this->setMessage($message);
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    /**
     * @param string $message
     */
    private function setMessage($message)
    {
        Validators::assert($message, 'unicode:1..255');
        $this->message = $message;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    public function getMessage()
    {
        return $this->message;
    }


    /*
     * ------------------------
     * ----- TYPE GETTERS -----
     * ------------------------
     */


    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->type->getId();
    }


    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->type->getName();
    }

}