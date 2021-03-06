<?php

namespace blitzik;

use Nette\Application\UI\Control;
use Nette\Utils\Validators;
use Nette\Utils\Paginator;

class VisualPaginator extends Control
{
    public $onPaginate = [];


    /** @var Paginator */
    private $paginator = null;

    /** @persistent */
    public $page = 1;

    /** @var  string */
    private $anchor;

    private $counter = true;
    private $borderPages = true;

    private $ajaxified = true;

    private $template;

    /** @var array */
    private $buttons = [
        'next' => 'next »',
        'previous' => '« previous'
    ];


    public function __construct()
    {
        $this->template = __DIR__ . '/ajax.latte';
    }


    public function setButtonText($button, $text)
    {
        $this->buttons[$button] = $text;
    }


    public function hideCounter()
    {
        $this->counter = false;
    }


    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }


    public function setPage($page)
    {
        $this->page = $page;
        $this->getPaginator()->setPage($page);
    }


    public function notAjaxified()
    {
        $this->template = __DIR__ . '/common.latte';
    }


    /**
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        if ($this->paginator == null) {
            $this->paginator = new Paginator;
        }

        return $this->paginator;
    }


    /**
     * Renders paginator
     * @return Void
     */
    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile($this->template);

        $paginator = $this->getPaginator();

        $template->paginator = $paginator;

	    $template->counter = $this->counter;
	    $template->borderPages = $this->borderPages;

        $template->buttons = $this->buttons;

        $template->render();
    }


    public function handlePaginate()
    {
        if ($this->presenter->isAjax()) {
            $this->redrawControl();
            $this->onPaginate($this);
        } else {
            $anchor = isset($this->anchor) ? '#'.$this->anchor : null;
            $this->redirect('this'.$anchor);
        }
    }


    public function loadState(array $params)
    {
        if (isset($params['page']) and !Validators::is($params['page'], 'numericint')) {
            $params['page'] = 1;
        }

        parent::loadState($params);
        $this->getPaginator()->setPage($this->page);
        $this->page = $this->getPaginator()->getPage();
    }

}