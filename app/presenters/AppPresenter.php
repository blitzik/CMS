<?php

namespace App\Presenters;

use App\Components\IFlashMessagesControlFactory;
use Nette\Application\UI\Presenter;
use Options\Facades\OptionFacade;
use Users\User;

abstract class AppPresenter extends Presenter
{
    /**
     * @var IFlashMessagesControlFactory
     * @inject
     */
    public $flashMessagesFactory;

    /**
     * @var OptionFacade
     * @inject
     */
    public $optionFacade;

    /** @var  User */
    protected $userEntity;

    /** @var  array */
    protected $options;

    protected function startup()
    {
        parent::startup();

        $this->userEntity = $this->user->getIdentity();
        $this->options = $this->optionFacade->loadOptions();
    }

    protected function createComponentFlashMessages()
    {
        $comp = $this->flashMessagesFactory->create();

        return $comp;
    }

}