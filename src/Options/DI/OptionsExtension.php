<?php

namespace Options\DI;

use App\Extensions\CompilerExtension;
use App\Fixtures\IFixtureProvider;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Translation\DI\ITranslationProvider;
use Options\Fixtures\OptionsFixture;

class OptionsExtension extends CompilerExtension implements IEntityProvider, ITranslationProvider, IFixtureProvider
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);
    }

    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Options' => 'Options\\*Module\\Presenters\\*Presenter']);
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return ['Options' => __DIR__ . '/..'];
    }


    /**
     * Return array of directories, that contain resources for translator.
     *
     * @return string[]
     */
    function getTranslationResources()
    {
        return [
            __DIR__ . '/../lang'
        ];
    }


    /**
     * @return array
     */
    public function getDataFixtures()
    {
        return [
            __DIR__ . '/../fixtures/basic' => [
                OptionsFixture::class
            ]
        ];
    }

}