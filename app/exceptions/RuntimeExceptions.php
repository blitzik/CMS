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

    // FILEs
    class FileUploadException extends RuntimeException {}

        class NotImageUploadedException extends FileUploadException {}

        class FileSizeException extends FileUploadException {}

        class FileNameException extends FileUploadException {}

    class FileRemovalException extends RuntimeException {}