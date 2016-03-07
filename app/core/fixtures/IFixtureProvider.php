<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 07.03.2016
 */

namespace App\Fixtures;

interface IFixtureProvider
{
    /**
     * @return array
     */
    public function getDataFixtures();
}