<?php

namespace App\Components;

use blitzik\FlashMessages\TFlashMessages;
use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{
    use TFlashMessages;

    /** @var  IFlashMessagesControlFactory */
    protected $flashMessagesFactory;


    /**
     * @param IFlashMessagesControlFactory $factory
     */
    public function injectFlashMessagesFactory(IFlashMessagesControlFactory $factory)
    {
        $this->flashMessagesFactory = $factory;
    }


    protected function createComponentFlashMessages()
    {
        $comp = $this->flashMessagesFactory->create();

        return $comp;
    }
}