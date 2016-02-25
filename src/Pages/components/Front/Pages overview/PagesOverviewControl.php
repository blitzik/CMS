<?php

namespace Pages\Components\Front;

use App\Components\BaseControl;
use blitzik\IPaginatorFactory;
use Doctrine\ORM\AbstractQuery;
use Nette\Application\UI\Multiplier;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Nette\Utils\Paginator;
use Nette\Utils\Validators;
use Pages\Facades\PageFacade;
use Pages\Query\PageQuery;

class PagesOverviewControl extends BaseControl
{
    /** @var array */
    public $onPaginate = [];

    /** @var PageFacade */
    private $pageFacade;

    /** @var IPageControlFactory */
    private $pageControlFactory;

    /** @var  array */
    private $pages;

    /** @var IPaginatorFactory */
    private $paginatorFactory;

    /** @var  int */
    private $pagesPerPage;


    public function __construct(
        PageFacade $pageFacade,
        IPageControlFactory $pageControlFactory,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->pageFacade = $pageFacade;
        $this->pageControlFactory = $pageControlFactory;
        $this->paginatorFactory = $paginatorFactory;
    }


    public function setPagesPerPage($pagesPerPage)
    {
        Validators::assert($pagesPerPage, 'numericint:1..');

        $this->pagesPerPage = $pagesPerPage;
    }


    protected function createComponentVs()
    {
        $comp = $this->paginatorFactory->create();
        $comp->hideCounter();
        $comp->notAjaxified();

        return $comp;
    }


    protected function createComponentPage()
    {
        return new Multiplier(function ($pageID) {
            $comp = $this->pageControlFactory->create($this->pages[$pageID]);
            $comp->onlyIntro();

            return $comp;
        });
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/overview.latte');

        /** @var Paginator $paginator */
        $paginator = $this['vs']->getPaginator();

        $this->onPaginate($paginator);

        $resultSet = $this->pageFacade
                          ->fetchPages(
                              (new PageQuery())
                               ->forOverview()
                               ->withTags()
                               ->onlyPublished()
                               ->orderByPublishedAt('DESC')
                               ->indexedByPageId()
                          );

        $resultSet->applyPaginator($paginator, $this->pagesPerPage);

        $this->pages = $resultSet->toArray();

        $template->pages = $this->pages;
        $template->pagesCount = count($this->pages);

        $template->render();
    }
}


interface IPagesOverviewControlFactory
{
    /**
     * @return PagesOverviewControl
     */
    public function create();
}