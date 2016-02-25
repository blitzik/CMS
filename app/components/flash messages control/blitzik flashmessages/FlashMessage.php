<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 21.02.2016
 */

namespace blitzik\FlashMessages;

use Nette\Localization\ITranslator;
use Nette\Object;

class FlashMessage extends Object
{
    const INFO = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR = 'error';


    /** @var ITranslator */
    private $translator;

    /** @var string */
    private $message;

    /** @var Phrase */
    private $phrase;

    /** @var string */
    private $type;


    public function __construct(ITranslator $translator, IPhrase $phrase)
    {
        $this->translator = $translator;
        $this->phrase = $phrase;
    }


    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return null|string
     */
    public function getMessage()
    {
        if ($this->message === null and $this->translator !== null) {
            $this->message = $this->phrase->translate($this->translator);
        }

        return $this->message;
    }


    public function __sleep()
    {
        $this->message = $this->getMessage();
        return ['message', 'type'];
    }
}