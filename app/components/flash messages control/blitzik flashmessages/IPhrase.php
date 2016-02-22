<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 21.02.2016
 */

namespace blitzik\FlashMessages;

use Nette\Localization\ITranslator;

interface IPhrase
{
    public function translate(ITranslator $translator);
}