<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 23.3.2016
 * Time: 11:11
 */

namespace Users\Authorization;

use Users\User;

interface IAuthorizator
{
    /**
     * @param IAuthorizationDefinition $authorizationDefinition
     * @return void
     */
    public function addDefinition(IAuthorizationDefinition $authorizationDefinition);


    /**
     * @param User $user
     * @param string $resource
     * @param string $privilege
     * @return mixed
     */
    public function isAllowed(User $user, $resource, $privilege);
}