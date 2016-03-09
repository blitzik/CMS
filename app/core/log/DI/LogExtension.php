<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 06.03.2016
 */

namespace Log\DI;

use App\Fixtures\IFixtureProvider;
use Kdyby\Doctrine\DI\IEntityProvider;
use App\Extensions\CompilerExtension;
use Log\Fixtures\LogFixture;

class LogExtension extends CompilerExtension implements IEntityProvider
{
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/services.neon'), $this->name);
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