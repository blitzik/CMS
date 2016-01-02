<?php

namespace App\Exceptions\Runtime;

class RuntimeException extends \RuntimeException {};


    // Articles
    class PageTitleAlreadyExistsException extends RuntimeException {}

    class PagePublicationException extends RuntimeException {}

    // URLs
    class UrlAlreadyExistsException extends RuntimeException {}

    // TAGs
    class TagNameAlreadyExistsException extends RuntimeException {}

    // FILEs
    class FileUploadException extends RuntimeException {}

        class NotImageUploadedException extends FileUploadException {}

        class FileSizeException extends FileUploadException {}

        class FileNameException extends FileUploadException {}

    class FileRemovalException extends RuntimeException {}