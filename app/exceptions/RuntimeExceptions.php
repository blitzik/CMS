<?php

namespace App\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {};

    // Pages
    class ArticleTitleAlreadyExistsException extends RuntimeException {}


    // URLs
    class UrlAlreadyExistsException extends RuntimeException {}