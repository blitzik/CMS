<?php

namespace App\Components;

use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{
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