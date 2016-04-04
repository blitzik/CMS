<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Log\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use App\Extensions\CompilerExtension;
use Log\Services\AppEventLogger;

class LogExtension extends CompilerExtension implements IEntityProvider
{
    /** @var array */
    private $defaults = [
        'eventsToSkip' => []
    ];


    public function loadConfiguration()
    {
        $config = $this->getConfig() + $this->defaults;
        $this->setConfig($config);

        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/services.neon'), $this->name);

        $appEventLogger = $cb->addDefinition($this->prefix('appEventLogger'));
        $appEventLogger->setClass(AppEventLogger::class);
        $appEventLogger->addSetup('addEventsToSkip', ['eventsToSkip' => $config['eventsToSkip']]);
    }


    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();
    }


    function getEntityMappings()
    {
        return ['Log' => __DIR__ . '/..'];
    }

}