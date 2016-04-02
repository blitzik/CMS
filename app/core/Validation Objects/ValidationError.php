<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 04.02.2016
 */

namespace App\ValidationObjects;

use Nette\Utils\Validators;
use Nette\Object;

class ValidationError extends Object implements ILoggableError
{
    /** @var string */
    private $index;

    /** @var string */
    private $loggerChannel;

    /** @var string */
    private $errorImportance;

    /** @var bool */
    private $isLoggable = false;

    /** @var string */
    private $message;

    /** @var string */
    private $type;


    /**
     * ValidationError constructor.
     * @param string $message
     * @param string $messageType Message error type (e.g for flash messages)
     */
    public function __construct($message, $messageType)
    {
        $this->message = $message;
        $this->type = $messageType;
    }


    /**
     * @param string $indexName
     */
    public function setIndex($indexName)
    {
        Validators::assert($indexName, 'unicode:1..');
        $this->index = $indexName;
    }


    /**
     * @param string $importance
     * @param string $loggerChannel
     */
    public function setAsLoggable($importance, $loggerChannel)
    {
        Validators::assert($importance, 'unicode:1..');
        $this->errorImportance = $importance;

        Validators::assert($loggerChannel, 'unicode:1..');
        $this->loggerChannel = $loggerChannel;

        $this->isLoggable = true;
    }


    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }


    /**
     * @return string
     */
    public function getLoggerChannel()
    {
        return $this->loggerChannel;
    }


    /**
     * @return string
     */
    public function getErrorImportance()
    {
        return ucfirst(strtolower($this->errorImportance));
    }


    /**
     * @return bool
     */
    public function isLoggable()
    {
        return $this->isLoggable;
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}