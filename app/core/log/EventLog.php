<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
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
 * @ORM\Table(name="log_event")
 *
 */
class EventLog
{
    use Identifier;
    use MagicAccessors;


    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=false, unique=true)
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="LogType")
     * @ORM\JoinColumn(name="log_type", referencedColumnName="id", nullable=false)
     * @var LogType
     */
    private $logType;


    public function __construct(
        $name,
        LogType $logType
    ) {
        $this->setName($name);
        $this->logType = $logType;
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    /**
     * @param string $name
     */
    private function setName($name)
    {
        Validators::assert($name, 'unicode:1..100');
        $this->name = $name;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    public function getName()
    {
        return $this->name;
    }


    /*
     * ---------------------------
     * ----- LogType GETTERS -----
     * ---------------------------
     */


    /**
     * @return int
     */
    public function getLogTypeId()
    {
        return $this->logType->getId();
    }


    /**
     * @return string
     */
    public function getLogTypeName()
    {
        return $this->logType->getName();
    }


}