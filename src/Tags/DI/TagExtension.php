<?php

namespace Tags;

use App\Extensions\CompilerExtension;
use Kdyby\Doctrine\DI\IEntityProvider;

class TagsExtension extends CompilerExtension implements IEntityProvider
{
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