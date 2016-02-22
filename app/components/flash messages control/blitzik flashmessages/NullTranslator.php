<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 21.02.2016
 */

namespace blitzik\FlashMessages;

use Nette\Localization\ITranslator;
use Nette\Localization\message;
use Nette\Localization\plural;
use Nette\Object;

class NullTranslator extends Object implements ITranslator
{
    /**
     * Translates the given string.
     * @param  string   message
     * @param  int      plural count
     * @return string
     */
    function translate($message, $count = null)
    {
        return $message;
    }

}