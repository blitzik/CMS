<?php

namespace App\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {};


    // Pages
    class ArticleTitleAlreadyExistsException extends RuntimeException {}

    class ArticlePublicationException extends RuntimeException {}

    // URLs
    class UrlAlreadyExistsException extends RuntimeException {}

    // TAGs
    class TagNameAlreadyExistsException extends RuntimeException {}