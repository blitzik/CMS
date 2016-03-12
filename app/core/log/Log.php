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
use Users\User;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="log",
 *     indexes={@Index(name="event_id", columns={"event", "id"})}
 * )
 */
class Log
{
    use Identifier;
    use MagicAccessors;

    const CACHE_NAMESPACE = 'eventLogging';

    /**
     * @ORM\Column(name="`date`", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="EventLog")
     * @ORM\JoinColumn(name="event", referencedColumnName="id", nullable=false)
     * @var EventLog
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Users\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=true)
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(name="message", type="string", length=1000, nullable=false, unique=false)
     * @var string
     */
    private $message;


    public function __construct(
        $message,
        EventLog $eventLog,
        User $user = null
    ) {
        $this->setMessage($message);
        $this->event = $eventLog;
        $this->user = $user;

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
        Validators::assert($message, 'unicode:1..1000');
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
     * -------------------------
     * ----- EVENT GETTERS -----
     * -------------------------
     */


    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->event->getId();
    }


    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->event->getName();
    }


    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->event->getLogTypeId();
    }


    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->event->getLogTypeName();
    }

}