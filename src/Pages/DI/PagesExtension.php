<?php

namespace Pages\DI;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;

class PagesExtension extends CompilerExtension implements IEntityProvider
{
    /**
     * Adjusts DI container before is compiled to PHP class. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Pages' => 'Pages\\*Module\\Presenters\\*Presenter']);
    }

    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    public function getEntityMappings()
    {
        return ['Pages' => __DIR__ . '/..'];
    }

}