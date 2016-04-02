<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 04.02.2016
 */

namespace App\ValidationObjects\Factories;

use App\ValidationObjects\ValidationObject;
use Nette\Object;

class ValidationObjectFactory extends Object
{
    /**
     * @param string|null $loggerChannel
     * @return ValidationObject
     */
    public function create($loggerChannel = null)
    {
        $validationObject = new ValidationObject($loggerChannel);

        return $validationObject;
    }
}