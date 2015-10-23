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

    private $counter = true;
    private $borderPages = true;


    public function hideCounter()
    {
        $this->counter = false;
    }


    public function setpage($page)
    {
        $this->page = $page;
        $this->getPaginator()->setPage($page);
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

        $this->template->setFile(dirname(__FILE__) . '/template.latte');
        $this->template->render();
    }

    public function handlePaginate()
    {
        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->redirect('this');
        }

        $this->onPaginate($this);
    }

    public function loadState(array $params)
    {
        parent::loadState($params);
        $this->getPaginator()->page = $this->page;
    }

}