<?php

namespace Pages\AdminModule\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Pages\Components\Admin\IPagesOverviewControlFactory;
use Pages\Components\Admin\PageFormControl;
use Pages\Components\Admin\PageRemovalControl;
use Pages\Components\Admin\IPageFormControlFactory;
use Pages\Components\Admin\IPageRemovalControlFactory;
use Pages\Components\Admin\PagesOverviewControl;
use Pages\Facades\PageFacade;
use Pages\Page;
use Pages\Query\PageQuery;
use Url\Facades\UrlFacade;

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
            $this->flashMessage('Požadovaný článek nebyl nalezen.', 'warning');
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
        $this['pageTitle']->setPageTitle('Přehled článků');
    }


    public function renderOverview()
    {
    }


    protected function createComponentPublishedPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
            ->create(
                (new PageQuery())
                 ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                 ->onlyPublished()
                 ->withTags()
                 ->orderByPublishedAt('DESC')
            );

        $comp->setTitle('Publikované články');
        $comp->setPrependTitleIcon('eye');

        $comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }


    protected function createComponentWaitingPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
            ->create(
                (new PageQuery())
                 ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                 ->waitingForBeingPublished()
                 ->withTags()
            );

        $comp->setTitle('Články čekající na zveřejnění');
        $comp->setPrependTitleIcon('hourglass-half');

        $comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }


    protected function createComponentUnpublishedPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
            ->create(
                (new PageQuery())
                 ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                 ->notPublished()
                 ->withTags()
                 ->orderByPublishedAt('DESC')
            );

        $comp->setTitle('Nepublikované články');
        $comp->setPrependTitleIcon('eye-slash');

        $comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }


    public function onToggleVisibility(PagesOverviewControl $control)
    {
        if ($this->isAjax()) {
            $control->redrawControl('table');
            $this->redrawControl('pagesTables');
        } else {
            $this->redirect('this#'.$control->getUniqueId());
        }
    }


    /*
     * ----------------------------
     * ----- ARTICLE CREATION -----
     * ----------------------------
     */


    public function actionNew()
    {
        $this['pageTitle']->setPageTitle('Nový článek');
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

        $this['pageTitle']->setPageTitle('Editace článku')
                          ->joinTitleText(' - ' . $this->page->title);

        $this['articleForm']->setPageToEdit($this->page);
    }


    public function renderEdit($id)
    {
        $this->template->page = $this->page;
    }


    protected function createComponentArticleForm()
    {
        $comp = $this->pageFormFactory->create($this->userEntity);
        $comp->onSuccessPageSaving[] = [$this, 'onSuccessPageSaving'];

        return $comp;
    }


    public function onSuccessPageSaving(PageFormControl $pageFormControl, Page $page)
    {
        if ($this->isAjax()) {
            $pageFormControl->redrawControl();
        } else {
            $this->redirect('Page:edit', ['id' => $page->getId()]);
        }
    }


    /*
     * --------------------------
     * ----- REMOVE ARTICLE -----
     * --------------------------
     */


    public function actionRemove($id)
    {
        $this->page = $this->getPage($id);

        $this['pageTitle']->setPageTitle('Smazání článku')
                          ->joinTitleText(' - ' . $this->page->title);
    }


    public function renderRemove($id)
    {
        $this->template->page = $this->page;
    }


    protected function createComponentArticleRemovalForm()
    {
        $comp = $this->pageRemovalFactory->create($this->page);

        $comp->onPageRemoval[] = [$this, 'onArticleRemoval'];
        $comp->onCancelClick[] = [$this, 'onCancelClick'];

        return $comp;
    }


    public function onArticleRemoval(PageRemovalControl $control)
    {
        $this->flashMessage('Článek byl úspěšně smazán', 'success');
        $this->redirect(':Dashboard:Dashboard:default');
    }


    public function onCancelClick(PageRemovalControl $control)
    {
        $this->redirect(':Dashboard:Dashboard:default');
    }
}