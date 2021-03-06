<?php

namespace Pages\Components\Admin;

use App\Components\BaseControl;
use Pages\Facades\PageFacade;
use blitzik\VisualPaginator;
use Nette\Utils\Paginator;
use Pages\Query\PageQuery;

class PagesOverviewControl extends BaseControl
{
    /** @var array  */
    //public $onToggleVisibility = [];


    /** @var PageFacade  */
    private $pageFacade;

    /** @var PageQuery  */
    private $pageQuery;

    /** @var  string */
    private $title;

    /** @var  string */
    private $icon;

    /** @var bool */
    private $hiddenOnNoPages = true;

    /** @var  int */
    private $pagesCount = 10;


    public function __construct(
        PageQuery $pageQuery,
        PageFacade $pageFacade
    ) {
        $this->pageFacade = $pageFacade;
        $this->pageQuery = $pageQuery;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @param string $icon font-awesome icon name
     */
    public function setPrependTitleIcon($icon)
    {
        $this->icon = $icon;
    }


    public function setPagesPerPage($pagesCount)
    {
        $this->pagesCount = $pagesCount;
    }


    public function showOnNoPages()
    {
        $this->hiddenOnNoPages = false;
    }


    protected function createComponentVs()
    {
        $vs = new VisualPaginator();
        //$vs->notAjaxified();
        $vs->setAnchor($this->getUniqueId());

        $vs->onPaginate[] = function () {
            $this->redrawControl('table');
        };


        return $vs;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/overview.latte');

        $resultSet = $this->pageFacade
                          ->fetchPages($this->pageQuery);

        /** @var Paginator $paginator */
        $paginator = $this['vs']->getPaginator();

        $resultSet->applyPaginator($paginator, $this->pagesCount);

        $pages = $resultSet->toArray();

        $template->pages = $pages;
        $template->pagesCount = count($pages);
        $template->title = $this->title;
        $template->icon = $this->icon;
        $template->hiddenOnNoPages = $this->hiddenOnNoPages;

        $template->render();
    }


    /*public function handlePublishArticle($id)
    {
        $this->pageFacade->publishPage($id);

        $this->flashMessage('Článek byl úspěšně publikován.', 'success');
        $this->onToggleVisibility($this);
    }


    public function handleHideArticle($id)
    {
        $this->pageFacade->hidePage($id);

        $this->flashMessage('Článek již není veřejné přístupný.', 'success');
        $this->onToggleVisibility($this);
    }*/
}


interface IPagesOverviewControlFactory
{
    /**
     * @param PageQuery $pageQuery
     * @return PagesOverviewControl
     */
    public function create(PageQuery $pageQuery);
}