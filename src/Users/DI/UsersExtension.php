<?php

namespace  Users;

use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\DI\CompilerExtension;

class UsersExtension extends CompilerExtension implements IEntityProvider
{
    /**
     * Returns associative array of Namespace => mapping definition
     *
     * @return array
     */
    function getEntityMappings()
    {
        return ['Users' => __DIR__ . '/..'];
    }

}