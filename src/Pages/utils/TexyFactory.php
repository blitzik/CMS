<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 25.02.2016
 */

namespace Pages\Utils;

use FSHL\Output\HtmlManual;
use FSHL\Highlighter;
use FSHL\Lexer;
use Nette\Http\Request;

class TexyFactory
{
    /** @var Request */
    private $request;

    /** @var string */
    private $imagesRoot;

    /** @var string */
    private $imagesFileRoot;

    /** @var array */
    private $lexers = [
        'block/cpp' => Lexer\Cpp::class,
        'block/css' => Lexer\Css::class,
        'block/html' => Lexer\Html::class,
        'block/java' => Lexer\Java::class,
        'block/js' => Lexer\Javascript::class,
        'block/neon' => Lexer\Neon::class,
        'block/php' => Lexer\Php::class,
        'block/python' => Lexer\Python::class,
        'block/sql'=> Lexer\Sql::class,
        'block/texy' => Lexer\Texy::class
    ];

    /** @var Highlighter */
    private $highlighter;

    /** @var Lexer */
    private $lexersInstances = [];


    public function __construct(
        $imagesRoot,
        $imagesFileRoot,
        Request $request
    ) {
        $this->imagesRoot = $imagesRoot;
        $this->imagesFileRoot = $imagesFileRoot;
        $this->request = $request;
    }


    /**
     * @return \Texy
     */
    public function createTexyForPage()
    {
        $texy = new \Texy();

        $texy->headingModule->top = 3;
        $texy->setOutputMode(\Texy::HTML5);

        // Images
        $texy->imageModule->root = $this->request->getUrl()->getBaseUrl() . $this->imagesRoot;
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

        $texy->headingModule->top = 3;
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
        /** @var \Texy $texy */
        $texy = $invocation->getTexy();

        $content = \Texy::outdent($content);

        $lexerName = $this->resolveLexerName($blocktype);
        $lexer = $this->getLexerInstance($lexerName);

        $highlighter = $this->getHighlighter();
        if ($lexer !== false) {
            $content = $highlighter->highlight($content, $lexer);
        } else {
            $content = htmlspecialchars($content);
        }

        $content = $texy->protect($content, \Texy::CONTENT_BLOCK);

        $elPre = \TexyHtml::el('pre');

        if ($modifier) $modifier->decorate($texy, $elPre);

        $elPre->attrs['class'] = mb_strtolower($this->getLanguage($blocktype));
        $elPre->create('code', $content);

        return $elPre;
    }


    /**
     * @param string $blocktype
     * @return string returns class name
     */
    private function resolveLexerName($blocktype)
    {
        $lang = mb_strtolower($blocktype);
        $lexer = null;
        if (array_key_exists($lang, $this->lexers)) {
            return $this->lexers[$lang];
        }

        return Lexer\Minimal::class;
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
     * @return Lexer|false Returns false if there is no lexer class with given name
     */
    private function getLexerInstance($lexerName)
    {
        if (!isset($this->lexersInstances[$lexerName])) {
            if (class_exists($lexerName)) {
                $this->lexersInstances[$lexerName] = new $lexerName;
            } else {
                return false;
            }
        }

        return $this->lexersInstances[$lexerName];
    }


    /**
     * @param string $blocktype
     * @return string
     */
    private function getLanguage($blocktype)
    {
        return mb_substr($blocktype, mb_strrpos($blocktype, '/') + 1);
    }

}