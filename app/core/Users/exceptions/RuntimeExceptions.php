<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 31.03.2016
 */

namespace Users\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {}

    class RoleAlreadyExistsException extends RuntimeException {}

    class RoleMissingException extends RuntimeException {}