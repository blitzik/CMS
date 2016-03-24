<?php

namespace App\Components;

use blitzik\FlashMessages\TFlashMessages;
use Nette\Application\UI\Control;
use Nette\Security\IAuthorizator;
use Users\User;

abstract class BaseControl extends Control
{
    use TFlashMessages;

    /** @var  IFlashMessagesControlFactory */
    protected $flashMessagesFactory;

    /** @var IAuthorizator */
    protected $authorizator;

    /** @var \Nette\Security\User */
    protected $user;


    public function setAuthorizator(IAuthorizator $authorizator)
    {
        $this->authorizator = $authorizator;
    }


    public function setUser(\Nette\Security\User $user)
    {
        $this->user = $user;
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