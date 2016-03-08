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
     * @ORM\Column(name="`date`", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="LogType")
     * @ORM\JoinColumn(name="type", referencedColumnName="id", nullable=false)
     * @var LogType
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="EventLog")
     * @ORM\JoinColumn(name="event", referencedColumnName="id", nullable=false)
     * @var EventLog
     */
    private $event;

    /**
     * @ORM\Column(name="message", type="string", length=1000, nullable=false, unique=false)
     * @var string
     */
    private $message;


    public function __construct(
        LogType $type,
        EventLog $event,
        $message
    ) {
        $this->type = $type;
        $this->event = $event;
        $this->setMessage($message);

        $this->date = new \DateTime('now');
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


    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return clone $this->date;
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