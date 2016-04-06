<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 05.04.2016
 */

namespace Users\Authorization;

interface IRole extends \Nette\Security\IRole
{
    /**
     * @return int
     */
    public function getId();
}