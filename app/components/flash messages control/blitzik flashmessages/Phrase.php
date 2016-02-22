<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 21.02.2016
 */

namespace blitzik\FlashMessages;

use Nette\Localization\ITranslator;
use Nette\Object;

class Phrase extends Object implements IPhrase
{
    /** @var array */
    private $parameters;

    /** @var string */
    private $message;

    /** @var null */
    private $count;


    public function __construct($message, $count = null, array $parameters = [])
    {
        $this->message = $message;
        $this->count = $count;
        $this->parameters = $parameters;
    }


    /**
     * @param ITranslator $translator
     * @return string
     */
    public function translate(ITranslator $translator)
    {
        return $translator->translate($this->message, $this->count, $this->parameters);
    }

}