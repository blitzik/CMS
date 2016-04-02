<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 04.02.2016
 */

namespace App\ValidationObjects;

use App\Common\RuntimeExceptions\ValidationErrorIndexAlreadyExistsException;
use Nette\Utils\Validators;
use Nette\Object;

/**
 * Class ValidationObject
 * @package ValidationObjects
 *
 * In order to log errors there must be set $loggerChannel and every
 * error that have to be logged must have $errorImportance set
 */
class ValidationObject extends Object
{
    /** @var string */
    private $loggerChannel;

    /** @var ValidationError[] */
    private $errors = [];
    
    /** @var mixed */
    private $result;


    public function __construct($loggerChannel = null)
    {
        $this->loggerChannel = $loggerChannel;
    }


    /**
     * @param string $message
     * @param string $type
     * @param string|null $index
     * @param string|null $errorImportance
     */
    public function addError($message, $type, $index = null, $errorImportance = null)
    {
        $error = new ValidationError($message, $type);
        if (Validators::is($errorImportance, 'unicode:1..')) {
            if (Validators::is($this->loggerChannel, 'unicode:1..')) {
                $error->setAsLoggable($errorImportance, $this->loggerChannel);
            }
        }

        if (Validators::is($index, 'unicode:1..')) {
            $error->setIndex($index);

            if (isset($this->errors[$index])) {
                throw new ValidationErrorIndexAlreadyExistsException(sprintf('Validation Object already contains an Error with index "%s"', $index));
            }
            $this->errors[$index] = $error;

        } else {
            $this->errors[] = $error;
        }
    }


    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }


    public function getResult()
    {
        return $this->result;
    }


    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }


    /**
     * @return ValidationError|bool Returns false if there is no errors
     */
    public function getLastAddedError()
    {
        return end($this->errors);
    }


    /**
     * @param $index
     * @return null|ValidationError
     */
    public function getErrorByIndex($index)
    {
        if (isset($this->errors[$index])) {
            return $this->errors[$index];
        }

        return null;
    }


    /**
     * @return ValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}