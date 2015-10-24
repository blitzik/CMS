<?php

namespace Pages\Utils;

/**
 * This class is used for processing Articles bodies only
 *
 * Class BlogTexy
 * @package Pages\Utils
 */
class BlogTexy extends \Texy
{
    public function __construct()
    {
        parent::__construct();

        $this->headingModule->top = 2;
        $this->setOutputMode(\Texy::HTML5);
    }
}