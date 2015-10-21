<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Users\User;

class BasePresenter extends Presenter
{
    /** @var  User */
    protected $userEntity;

    protected function startup()
    {
        parent::startup();

        $this->userEntity = $this->user->getIdentity();
    }
}