<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Pages\Utils;

use FSHL\Lexer;
use FSHL\Output\HtmlManual;
use FSHL\Highlighter;

class TexyFactory
{
    /** @var string */
    private $imagesRoot;

    /** @var string */
    private $imagesFileRoot;

    /** @var array */
    private $lexers = [
        'c' => 'Cpp',
        'cpp' => 'Cpp',
        'css' => 'Css',
        'html' => 'Html',
        'htmlonly' => 'HtmlOnly',
        'java' => 'Java',
        'javascript' => 'Javascript',
        'js' => 'Javascript',
        'neon' => 'Neon',
        'php' => 'Php',
        'python' => 'Python',
        'py' => 'Python',
        'sql'=> 'Sql',
        'texy' => 'Texy'
    ];

    /** @var Highlighter */
    private $highlighter;

    /** @var Lexer */
    private $lexersInstances = [];


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

        $texy->addHandler('block', [$this, 'blockHandler']);

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

        $texy->linkModule->forceNoFollow = true;

        $texy->addHandler('block', [$this, 'blockHandler']);

        return $texy;
    }


    /**
     * User handler for code block
     *
     * @param TexyHandlerInvocation  handler invocation
     * @param string  block type
     * @param string  text to highlight
     * @param string  language
     * @param TexyModifier modifier
     * @return TexyHtml
     */
    public function blockHandler($invocation, $blocktype, $content, $lang, $modifier)
    {
        if ($blocktype !== 'block/code') {
            return $invocation->proceed();
        }

        /** @var \Texy $texy */
        $texy = $invocation->getTexy();

        $content = \Texy::outdent($content);

        $lexerName = $this->resolveLexerName($lang);
        $lexer = $this->getLexerInstance($lexerName);

        $highlighter = $this->getHighlighter();
        $content = $highlighter->highlight($content, $lexer);

        $content = $texy->protect($content, \Texy::CONTENT_BLOCK);

        $elPre = \TexyHtml::el('pre');

        if ($modifier) $modifier->decorate($texy, $elPre);

        $elPre->attrs['class'] = strtolower(mb_strtolower($lexerName));
        $elPre->create('code', $content);

        return $elPre;
    }


    /**
     * @param string $lang
     * @return string
     */
    private function resolveLexerName($lang)
    {
        $lang = mb_strtolower($lang);
        $lexer = null;
        if (array_key_exists($lang, $this->lexers)) {
            return $this->lexers[$lang];
        }

        return 'Minimal';
    }


    /**
     * @return Highlighter
     */
    private function getHighlighter()
    {
        if (!isset($this->highlighter)) {
            $this->highlighter = new Highlighter(new HtmlManual(), Highlighter::OPTION_TAB_INDENT);
        }

        return $this->highlighter;
    }


    /**
     * @param $lexerName
     * @return Lexer
     */
    private function getLexerInstance($lexerName)
    {
        if (!isset($this->lexersInstances[$lexerName])) {
            $lexerObjectName = 'FSHL\Lexer\\' . $lexerName;
            $this->lexersInstances[$lexerName] = new $lexerObjectName;
        }

        return $this->lexersInstances[$lexerName];
    }

}