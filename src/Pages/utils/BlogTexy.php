<?php

namespace Pages\Utils;
use Images\Image;

class BlogTexy extends \Texy // todo relocate image paths into config
{
    public function __construct()
    {
        parent::__construct();

        $this->headingModule->top = 2;
        $this->setOutputMode(\Texy::HTML5);

        // Images
        $this->imageModule->root = './uploads/images/';
        $this->imageModule->fileRoot = Image::UPLOAD_DIRECTORY;
    }
}