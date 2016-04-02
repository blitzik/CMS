<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 04.02.2016
 */

namespace App\ValidationObjects;

interface ILoggableError
{
    /**
     * @return bool
     */
    public function isLoggable();
}