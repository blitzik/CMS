<?php

namespace Url\DI;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;

class UrlExtension extends CompilerExtension implements IEntityProvider
{
    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {

        $cb = $this->getContainerBuilder();

        $cb->removeDefinition('routing.router');

        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);
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