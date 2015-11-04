<?php

namespace Images\DI;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\DI\Statement;

class ImagesExtension extends CompilerExtension implements IEntityProvider
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
        $this->setPresenterMapping($cb, ['Images' => 'Images\\*Module\\Presenters\\*Presenter']);

        $latteFactory = $cb->getDefinition('latte.latteFactory');
        $latteFactory->addSetup('addFilter', [null, ['@Images\\Filters\\FilterLoader', 'loader']]);
    }


    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return ['Images' => __DIR__ . '/..'];
    }
}