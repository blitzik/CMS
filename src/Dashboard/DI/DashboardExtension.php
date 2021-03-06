<?php

namespace Dashboard\DI;

use App\Extensions\CompilerExtension;
use App\Fixtures\IFixtureProvider;
use Dashboard\Fixtures\DashboardFixture;
use Kdyby\Translation\DI\ITranslationProvider;

class DashboardExtension extends CompilerExtension implements ITranslationProvider, IFixtureProvider
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon', $this->name));
    }


    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Dashboard' => 'Dashboard\\*Module\\Presenters\\*Presenter']);
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
                DashboardFixture::class
            ]
        ];
    }
}