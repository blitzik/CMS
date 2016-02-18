<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.02.2016
 */

namespace Pages\Exceptions\Logic;

class LogicException extends \LogicException {}

    class InvalidArgumentException extends LogicException {}

    class DateTimeFormatException extends LogicException {}