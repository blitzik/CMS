<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Url\Subscribers;

use Log\Services\AppEventLogger;
use Kdyby\Events\Subscriber;
use Url\Services\UrlPath;
use Nette\Object;
use Url\Router;

class UrlSubscriber extends Object implements Subscriber
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
            Router::class . '::onUrlNotFound'
        ];
    }


    public function onUrlNotFound(UrlPath $urlPath)
    {
        $this->appEventLogger->saveLog(sprintf('[404] Url "%s" not found', $urlPath->getPath()), 'url', '404');
    }

}