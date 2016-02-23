<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.02.2016
 */

namespace Url\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {}

    class UrlAlreadyExistsException extends RuntimeException {}

    class UrlNotPersistedException extends RuntimeException {}

    class NoLocalesSetException extends RuntimeException {}