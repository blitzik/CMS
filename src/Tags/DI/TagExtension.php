<?php

namespace Tags;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;

class TagsExtension extends CompilerExtension implements IEntityProvider
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
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return ['Tags' => __DIR__ . '/..'];
    }

}