<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.02.2016
 */

namespace Pages\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {}

    class PageTitleAlreadyExistsException extends RuntimeException {}

    class PagePublicationTimeException extends RuntimeException {}

    class PagePublicationTimeMissingException extends RuntimeException {}

    class WrongPageCommentReaction extends RuntimeException {}

    class TagNameAlreadyExistsException extends RuntimeException {}