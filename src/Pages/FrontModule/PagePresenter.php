<?php

namespace Pages\FrontModule\Presenters;

use Comments\Components\Front\ICommentsOverviewControlFactory;
use Comments\Components\Front\ICommentFormControlFactory;
use Pages\Components\Front\IPagesOverviewControlFactory;
use Nette\Application\ForbiddenRequestException;
use Pages\Components\Front\IPageControlFactory;
use App\FrontModule\Presenters\BasePresenter;
use Nette\Application\BadRequestException;
use Pages\Facades\PageFacade;
use Pages\Page;
use Pages\Query\PageQuery;

class PagePresenter extends BasePresenter
{
    /**
     * @var ICommentsOverviewControlFactory
     * @inject
     */
    public $commentsOverviewFactory;

    /**
     * @var IPagesOverviewControlFactory
     * @inject
     */
    public $pagesOverviewFactory;

    /**
     * @var ICommentFormControlFactory
     * @inject
     */
    public $commentsFormFactory;

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
     * @var array [0 => Pages\Page, 'commentsCount' => int]
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


    /**
     * @Actions default
     */
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
        $result = $this->pageFacade
                     ->fetchPage(
                         (new PageQuery())
                          ->withTags()
                          ->withCommentsCount()
                          ->byPageId($internal_id)
                     );

        /** @var Page $page */
        $page = $result[0];

        if ($page === null) { // nothing found
            throw new BadRequestException;
        }

        // only owner of blog can see articles drafts
        if (($page->isDraft() === true or
            $page->getPublishedAt() > (new \DateTime('now'))) and
            !$this->user->isLoggedIn()) {
            throw new BadRequestException;
        }

        $this['pageTitle']->setPageTitle($this->options->blog_title)
                          ->joinTitleText(' - ' . $page->title);

        $this->page = $result;
    }


    public function renderShow($internal_id)
    {
        $this->template->page = $this->page[0];
    }


    /**
     * @Actions show
     */
    protected function createComponentPage()
    {
        $comp = $this-> pageFactory->create($this->page[0]);
        $comp->setCommentsCount($this->page['commentsCount']);
        return $comp;
    }


    /**
     * @Actions show
     */
    protected function createComponentCommentsOverview()
    {
        $comp = $this->commentsOverviewFactory->create($this->page[0]);
        return $comp;
    }


    /**
     * @Actions show
     */
    protected function createComponentCommentsForm()
    {
        $comp = $this->commentsFormFactory
                     ->create($this->page[0]);

        return $comp;
    }
}