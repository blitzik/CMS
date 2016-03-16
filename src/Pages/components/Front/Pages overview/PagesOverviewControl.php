<?php

namespace Pages\Components\Front;

use Nette\Application\IPresenter;
use Nette\Application\UI\Multiplier;
use App\Components\BaseControl;
use blitzik\IPaginatorFactory;
use Kdyby\Doctrine\ResultSet;
use Pages\Facades\PageFacade;
use Nette\Utils\Validators;
use Nette\Utils\Paginator;
use Pages\Query\PageQuery;

class PagesOverviewControl extends BaseControl
{
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


    /** @var ResultSet */
    private $resultSet;


    public function __construct(
        PageFacade $pageFacade,
        IPageControlFactory $pageControlFactory,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->pageFacade = $pageFacade;
        $this->pageControlFactory = $pageControlFactory;
        $this->paginatorFactory = $paginatorFactory;

        $this->resultSet = $this->pageFacade
                                ->fetchPages(
                                    (new PageQuery())
                                    ->forOverview()
                                    ->withTags()
                                    ->onlyPublished()
                                    ->orderByPublishedAt('DESC')
                                    ->withCommentsCount()
                                    ->indexedByPageId()
                                );
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

        $comp->setButtonText('previous', '« novější');
        $comp->setButtonText('next', 'starší »');

        // because presenter canonicalizaion
        // we need paginator to be set to know the range of pages in case
        // someone would try to insert some meaningless value into pagination parameter in URL
        $this->resultSet->applyPaginator($comp->getPaginator(), $this->pagesPerPage);

        return $comp;
    }


    protected function createComponentPage()
    {
        return new Multiplier(function ($pageID) {
            $comp = $this->pageControlFactory->create($this->pages[$pageID][0]);
            $comp->setCommentsCount($this->pages[$pageID]['commentsCount']);
            $comp->onlyIntro();

            return $comp;
        });
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/overview.latte');

        $this->resultSet->applyPaginator($this['vs']->getPaginator(), $this->pagesPerPage);
        $this->pages = $this->resultSet->toArray();

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