<?php

namespace App\Components;

use blitzik\FlashMessages\TFlashMessages;
use Nette\Application\UI\Control;
use Nette\Security\IAuthorizator;

abstract class BaseControl extends Control
{
    use TFlashMessages;

    /** @var  IFlashMessagesControlFactory */
    protected $flashMessagesFactory;

    /** @var IAuthorizator */
    protected $authorizator;


    public function setAuthorizator(IAuthorizator $authorizator)
    {
        $this->authorizator = $authorizator;
    }
    

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