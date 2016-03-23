<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 23.3.2016
 * Time: 9:52
 */

namespace Users\Authorization;

use Nette\Security\Permission;

/**
 * Class that implements this interface just adds
 * specific Roles, Resources and privileges to ACL
 * generated from database
 *
 * @package Users\Authorization
 */
interface IAuthorizationDefinition
{
    /**
     * @param Permission $permission
     * @return void
     */
    public function createDefinitions(Permission $permission);
}