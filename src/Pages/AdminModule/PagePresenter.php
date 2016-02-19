<?php

namespace Pages\AdminModule\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Pages\Components\PageFormControl;
use Pages\Components\PageRemovalControl;
use Pages\Components\IPageFormControlFactory;
use Pages\Components\IPageRemovalControlFactory;
use Pages\Facades\PageFacade;
use Pages\Page;

class PagePresenter extends ProtectedPresenter
{
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
            $this->redirect(':Dashboard:Dashboard:default');
        }

        return $article;
    }

    /*
     * ----------------------------
     * ----- ARTICLE CREATION -----
     * ----------------------------
     */

    public function actionNew()
    {

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

        $this['articleForm']->setPageToEdit($this->page);
    }

    public function renderEdit($id)
    {

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