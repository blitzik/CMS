<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 05.04.2016
 */

namespace Options\Log\Subscribers;

use Options\Services\OptionsPersister;
use Log\Services\AppEventLogger;
use Kdyby\Events\Subscriber;
use Nette\Security\User;
use Nette\Object;

class OptionsSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user;
    }


    function getSubscribedEvents()
    {
        return [
            OptionsPersister::class . '::onSuccessOptionsSaving'
        ];
    }


    public function onSuccessOptionsSaving()
    {
        /** @var \Users\User $user */
        $user = $this->user->getIdentity();
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                    'User [%s#%s] <b>has EDITED</b> web options',
                    $user->getId(),
                    $user->getUsername()
                 ),
                 'options_editing',
                 $user->getId()
             );
    }


}