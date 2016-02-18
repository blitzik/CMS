<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.02.2016
 */

namespace Images\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {}

    class FileRemovalException extends RuntimeException {}

    class FileUploadException extends RuntimeException {}

        class NotImageUploadedException extends FileUploadException {}

        class FileSizeException extends FileUploadException {}

        class FileNameException extends FileUploadException {}