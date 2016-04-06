<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 05.04.2016
 */

namespace Users\Authorization;

interface IResource extends \Nette\Security\IResource
{
    /**
     * @return int
     */
    public function getOwnerId();
}