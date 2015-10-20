<?php

namespace Images;


use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\DI\CompilerExtension;

class ImagesExtension extends CompilerExtension implements IEntityProvider
{
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