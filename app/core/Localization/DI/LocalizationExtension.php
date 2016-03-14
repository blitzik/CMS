<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 13.03.2016
 */

namespace Localization\DI;

use App\Fixtures\IFixtureProvider;
use Kdyby\Doctrine\DI\IEntityProvider;
use App\Extensions\CompilerExtension;
use Localization\Fixtures\LocalizationFixture;

class LocalizationExtension extends CompilerExtension implements IEntityProvider, IFixtureProvider
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
        return ['Localization' => __DIR__ . '/..'];
    }


    /**
     * @return array
     */
    public function getDataFixtures()
    {
        return [
            __DIR__ . '/../fixtures/basic' => [
                LocalizationFixture::class
            ]
        ];
    }


}