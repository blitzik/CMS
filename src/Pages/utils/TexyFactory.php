<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Pages\Utils;

class TexyFactory
{
    /** @var string */
    private $imagesRoot;

    /** @var string */
    private $imagesFileRoot;


    public function __construct($imagesRoot, $imagesFileRoot)
    {
        $this->imagesRoot = $imagesRoot;
        $this->imagesFileRoot = $imagesFileRoot;
    }


    /**
     * @return \Texy
     */
    public function createTexyForPage()
    {
        $texy = new \Texy();

        $texy->headingModule->top = 2;
        $texy->setOutputMode(\Texy::HTML5);

        // Images
        $texy->imageModule->root = '.' . $this->imagesRoot;
        $texy->imageModule->fileRoot = $this->imagesFileRoot;

        return $texy;
    }


    /**
     * @return \Texy
     */
    public function createTexyForComment()
    {
        $texy = new \Texy();

        $texy->headingModule->top = 2;
        $texy->setOutputMode(\Texy::HTML5);

        // Images
        $texy->imageModule->root = '.' . $this->imagesRoot;
        $texy->imageModule->fileRoot = $this->imagesFileRoot;

        \TexyConfigurator::safeMode($texy);
        \TexyConfigurator::disableImages($texy);

        //$texy->allowed['blockquote'] = false;
        $texy->allowed['emoticon'] = false;
        $texy->allowed['heading/underlined'] = false;
        $texy->allowed['heading/surrounded'] = false;
        $texy->allowed['horizline'] = false;
        //$texy->allowed['html/tag'] = false;
        $texy->allowed['html/comment'] = false;
        //$texy->allowed['list'] = false;
        //$texy->allowed['list/definition'] = false;
        $texy->allowed['script'] = false;
        $texy->allowed['table'] = false;

        return $texy;
    }
}