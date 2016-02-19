<?php

namespace Pages\FrontModule\Presenters;

use App\FrontModule\Presenters\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Utils\ArrayHash;
use Pages\Components\Front\IPageControlFactory;
use Pages\Components\Front\IPagesOverviewControlFactory;
use Pages\Facades\PageFacade;

class PagePresenter extends BasePresenter
{
    /**
     * @var IPagesOverviewControlFactory
     * @inject
     */
    public $pagesOverviewFactory;

    /**
     * @var IPageControlFactory
     * @inject
     */
    public $pageFactory;

    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var ArrayHash
     */
    private $page;


    /*
     * -----------------------------------------
     * ----- ARTICLES OVERVIEW BY CATEGORY -----
     * -----------------------------------------
     */


    public function actionDefault()
    {
    }


    public function renderDefault()
    {
    }


    protected function createComponentPagesOverview()
    {
        $comp = $this->pagesOverviewFactory->create();
        if (isset($this->options['articles_per_page'])) {
            $comp->setPagesPerPage($this->options['articles_per_page']);
        }

        /*$comp->onPaginate[] = function (Paginator $paginator) {
            $paginator->setPage($this->getParameter('p'));
        };*/

        return $comp;
    }


    /*
     * ------------------------------
     * ----- PARTICULAR ARTICLE -----
     * ------------------------------
     */


    public function actionShow($internal_id)
    {
        $page = $this->pageFacade->getPageAsArray($internal_id);
        if ($page === null) {
            throw new BadRequestException;
        }

        $this->page = ArrayHash::from($page);

        //$this['metas']
    }


    public function renderShow($internal_id)
    {
        $this->template->page = $this->page;
    }


    protected function createComponentPage()
    {
        $comp = $this-> pageFactory->create($this->page);
        return $comp;
    }

}