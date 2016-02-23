<?php

namespace Url\DI;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;
use Tracy\Bar;
use Tracy\Debugger;
use Url\RequestPanel;
use Url\RouterPanel;

class UrlExtension extends CompilerExtension implements IEntityProvider
{
    private $defaults = [
        'localization' => [
            'defaultLocale' => 'en',
            'locales' => [
                'en' => 'en',
                'cs' => 'cs'
            ]
        ]
    ];

    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $config = $this->getConfig() + $this->defaults;
        $this->setConfig($config);

        $cb = $this->getContainerBuilder();

        $cb->removeDefinition('routing.router');
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);

        $router = $cb->getDefinition($this->prefix('router'));
        $router->setArguments(['localization' => $config['localization']]);
    }


    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();

        $bar = $cb->getDefinition($cb->getByType(Bar::class));
        $bar->addSetup('addPanel', ['@'.$cb->getByType(RequestPanel::class)]);
    }

    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    public function getEntityMappings()
    {
        return ['Url' => __DIR__ . '/..'];
    }

}