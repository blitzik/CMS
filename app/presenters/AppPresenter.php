<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Options\Facades\OptionFacade;
use Users\User;

abstract class AppPresenter extends Presenter
{
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
}