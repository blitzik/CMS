<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 08.03.2016
 */

namespace Log;

interface ILogEventProvider
{
    public function getSubscribedEvents();
}