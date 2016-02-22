<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 21.02.2016
 */

namespace blitzik\FlashMessages;

use Kdyby\Translation\Phrase;
use Nette\Localization\ITranslator;

trait TFlashMessages
{
    /** @var ITranslator|null */
    private $flashMessagesTranslator;


    /**
     * Saves the message to template, that can be displayed after redirect.
     * @param  string|Phrase
     * @param  string
     * @param int $count
     * @param array $parameters
     * @return \stdClass
     */
    public function flashMessage($message, $type = 'info', $count = null, array $parameters = [])
    {
        $id = $this->getParameterId('flash');
        $messages = $this->getPresenter()->getFlashSession()->$id;

        if ($message instanceof Phrase) {
            $message = new \blitzik\FlashMessages\Phrase($message->message, $message->count, $message->parameters);
        } elseif (!$message instanceof IPhrase) {
            $message = new \blitzik\FlashMessages\Phrase($message, $count, $parameters);
        }

        $flash = new FlashMessage($this->getTranslator(), $message);
        $flash->setType($type);

        $messages[] = $flash;

        $this->getTemplate()->flashes = $messages;
        $this->getPresenter()->getFlashSession()->$id = $messages;

        return $flash;
    }


    /**
     * @param ITranslator $translator
     */
    public function injectFlashMessagesTranslator(ITranslator $translator)
    {
        $this->flashMessagesTranslator = $translator;
    }


    /**
     * @return NullTranslator|ITranslator
     */
    public function getTranslator()
    {
        if ($this->flashMessagesTranslator === null) {
            return new NullTranslator();
        }

        return $this->flashMessagesTranslator;
    }
}