<?php

namespace Pages\AdminModule\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Pages\Components\ArticleRemovalControl;
use Pages\Components\IArticleFormControlFactory;
use Pages\Components\IArticleRemovalControlFactory;
use Pages\Facades\PageFacade;
use Pages\Article;

class PagePresenter extends ProtectedPresenter
{
    /**
     * @var IArticleRemovalControlFactory
     * @inject
     */
    public $articleRemovalFactory;

    /**
     * @var IArticleFormControlFactory
     * @inject
     */
    public $articleFormFactory;

    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /** @var  Article */
    private $article;


    public function getArticle($id)
    {
        $article = $this->pageFacade->getArticle(intval($id));
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
        $this->article = $this->getArticle($id);

        $this['articleForm']->setArticleToEdit($this->article);
    }

    public function renderEdit($id)
    {

    }

    protected function createComponentArticleForm()
    {
        $comp = $this->articleFormFactory->create($this->userEntity);

        return $comp;
    }

    /*
     * --------------------------
     * ----- REMOVE ARTICLE -----
     * --------------------------
     */

    public function actionRemove($id)
    {
        $this->article = $this->getArticle($id);
    }
    
    public function renderRemove($id)
    {
        
    }

    protected function createComponentArticleRemovalForm()
    {
        $comp = $this->articleRemovalFactory->create($this->article);

        $comp->onArticleRemoval[] = [$this, 'onArticleRemoval'];
        $comp->onCancelClick[] = [$this, 'onCancelClick'];

        return $comp;
    }

    public function onArticleRemoval(ArticleRemovalControl $control)
    {
        $this->flashMessage('Článek byl úspěšně smazán', 'success');
        $this->redirect(':Dashboard:Dashboard:default');
    }

    public function onCancelClick(ArticleRemovalControl $control)
    {
        $this->redirect(':Dashboard:Dashboard:default');
    }
}