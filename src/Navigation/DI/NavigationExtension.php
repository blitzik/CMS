<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.01.2016
 */

namespace Navigations\DI;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;

class NavigationExtension extends CompilerExtension implements IEntityProvider
{
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/services.neon'), $this->name);
    }


    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();

        $this->setPresenterMapping($cb, ['Navigations' => 'Navigations\\*Module\\Presenters\\*Presenter']);
    }


    function getEntityMappings()
    {
        return ['Navigations' => __DIR__ . '/..'];
    }

}