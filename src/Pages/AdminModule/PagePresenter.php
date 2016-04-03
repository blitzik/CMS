<?php

namespace Pages\AdminModule\Presenters;

use Pages\Components\Admin\IPagesOverviewControlFactory;
use Pages\Components\Admin\IPageRemovalControlFactory;
use Pages\Components\Admin\IPageFormControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Pages\Components\Admin\PageRemovalControl;
use Pages\Components\Admin\PageFormControl;
use blitzik\FlashMessages\FlashMessage;
use Pages\Facades\PageFacade;
use Pages\Query\PageQuery;
use Pages\Page;

class PagePresenter extends ProtectedPresenter
{
    /**
     * @var IPagesOverviewControlFactory
     * @inject
     */
    public $pagesOverviewFactory;

    /**
     * @var IPageRemovalControlFactory
     * @inject
     */
    public $pageRemovalFactory;

    /**
     * @var IPageFormControlFactory
     * @inject
     */
    public $pageFormFactory;

    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /** @var  Page */
    private $page;


    public function getPage($id)
    {
        $article = $this->pageFacade->getPage(intval($id));
        if ($article === null) {
            $this->flashMessage('pages.messages.articleNotFound', FlashMessage::WARNING);
            $this->redirect(':Pages:Admin:Page:overview');
        }

        return $article;
    }


    /*
     * -----------------------------
     * ----- ARTICLES OVERVIEW -----
     * -----------------------------
     */


    public function actionOverview()
    {
        $this['pageTitle']->setPageTitle('pagesOverview.pageTitle');
    }


    public function renderOverview()
    {
    }


    /**
     * @Actions overview
     */
    protected function createComponentPublishedPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
                     ->create(
                         (new PageQuery())
                          ->forAdminOverview()
                          ->onlyPublished()
                          ->withTags()
                          ->withCommentsCount()
                          ->orderByPublishedAt('DESC')
                     );

        $comp->setTitle('pagesOverview.tableTitle.published');
        $comp->setPrependTitleIcon('eye');
        $comp->showOnNoPages();

        //$comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }


    /**
     * @Actions overview
     */
    protected function createComponentWaitingPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
                     ->create(
                         (new PageQuery())
                          ->forAdminOverview()
                          ->waitingForBeingPublished()
                          ->withTags()
                          ->withCommentsCount()
                     );

        $comp->setTitle('pagesOverview.tableTitle.waiting');
        $comp->setPrependTitleIcon('hourglass-half');

        //$comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }


    /**
     * @Actions overview
     */
    protected function createComponentDraftPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
                     ->create(
                         (new PageQuery())
                          ->forAdminOverview()
                          ->onlyDrafts()
                          ->withTags()
                          ->withCommentsCount()
                          ->orderByPublishedAt('DESC')
                     );

        $comp->setTitle('pagesOverview.tableTitle.draft');
        $comp->setPrependTitleIcon('edit');

        //$comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }


    /*
     * ----------------------------
     * ----- ARTICLE CREATION -----
     * ----------------------------
     */


    public function actionNew()
    {
        $this['pageTitle']->setPageTitle('pageEdit.title.new');
    }


    public function renderNew()
    {
    }


    /*
     * ---------------------------
     * ----- ARTICLE EDITING -----
     * ---------------------------
     */


    public function actionEdit($id)
    {
        $this->page = $this->getPage($id);

        $this['pageTitle']->setPageTitle('pageEdit.title.edit')
                          ->joinTitleText(' - ' . $this->page->getTitle());

        $this['articleForm']->setPageToEdit($this->page);
    }


    public function renderEdit($id)
    {
        $this->template->page = $this->page;
    }


    /**
     * @Actions new, edit
     */
    protected function createComponentArticleForm()
    {
        $comp = $this->pageFormFactory->create($this->userEntity);
        $comp->onSuccessPageSaving[] = [$this, 'onSuccessPageSaving'];

        return $comp;
    }


    public function onSuccessPageSaving(PageFormControl $pageFormControl, Page $page)
    {
        $this->redirect('Page:edit', ['id' => $page->getId()]);
    }


    /*
     * --------------------------
     * ----- REMOVE ARTICLE -----
     * --------------------------
     */


    public function actionRemove($id)
    {
        $this->page = $this->pageFacade
                           ->fetchPage(
                               (new PageQuery())
                                ->byPageId($id)
                                ->withCommentsCount()
                           );

        if ($this->page === null) {
            $this->flashMessage('pages.messages.articleNotFound', FlashMessage::WARNING);
            $this->redirect(':Pages:Admin:Page:overview');

        }

        $this['pageTitle']->setPageTitle('pageRemoval.title')
                          ->joinTitleText(' - ' . $this->page[0]->getTitle());
    }


    public function renderRemove($id)
    {
        $this->template->page = $this->page[0];
        $this->template->commentsCount = $this->page['commentsCount'];
    }


    /**
     * @Actions remove
     */
    protected function createComponentArticleRemovalForm()
    {
        $comp = $this->pageRemovalFactory->create($this->page[0]);

        $comp->onPageRemoval[] = [$this, 'onArticleRemoval'];
        $comp->onCancelClick[] = [$this, 'onCancelClick'];

        return $comp;
    }


    public function onArticleRemoval(PageRemovalControl $control, Page $page)
    {
        $this->flashMessage('pageRemoval.flashMessages.success', FlashMessage::SUCCESS, null, ['name' => $page->getTitle()]);
        $this->redirect(':Pages:Admin:Page:overview');
    }


    public function onCancelClick(PageRemovalControl $control)
    {
        $this->redirect(':Pages:Admin:Page:overview');
    }
}