<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Users\Log\Subscribers;

use Users\Authentication\UserAuthenticator;
use Users\Presenters\AuthPresenter;
use Log\Services\AppEventLogger;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Users\User;

class UserSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;


    public function __construct(AppEventLogger $appEventLogger)
    {
        $this->appEventLogger = $appEventLogger;
    }



    function getSubscribedEvents()
    {
        return [
            UserAuthenticator::class . '::onLoggedIn',
            AuthPresenter::class . '::onLoggedOut'
        ];
    }


    public function onLoggedIn(User $user, $ip)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has LOGGED INTO system with IP %s',
                     $user->getId(),
                     $user->username,
                     $ip
                 ),
                'user_login',
                 $user->getId()
             );
    }


    public function onLoggedOut(User $user, $ip)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has LOGGED OUT of the system with IP %s',
                     $user->getId(),
                     $user->username,
                     $ip
                 ),
                'user_logout',
                 $user->getId()
             );
    }

}