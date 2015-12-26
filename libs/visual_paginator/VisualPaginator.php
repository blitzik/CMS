<?php

namespace blitzik;

use Nette\Application\UI\Control;
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
        $this->ajaxified = false;
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
        $paginator = $this->getPaginator();

        $this->template->paginator = $paginator;

	    $this->template->counter = $this->counter;
	    $this->template->borderPages = $this->borderPages;

        $this->template->ajaxified = $this->ajaxified;

        $this->template->setFile(dirname(__FILE__) . '/template.latte');
        $this->template->render();
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
        parent::loadState($params);
        $this->getPaginator()->page = $this->page;
    }

}