<?php

namespace Pages\FrontModule\Presenters;

use App\FrontModule\Presenters\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Utils\ArrayHash;
use Pages\Components\IArticleControlFactory;
use Pages\Components\IArticlesOverviewControlFactory;
use Pages\Facades\PageFacade;
use Pages\Article;

class PagePresenter extends BasePresenter
{
    /**
     * @var IArticlesOverviewControlFactory
     * @inject
     */
    public $articlesOverviewFactory;

    /**
     * @var IArticleControlFactory
     * @inject
     */
    public $articleFactory;

    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var ArrayHash
     */
    private $article;

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

    protected function createComponentArticlesOverview()
    {
        $comp = $this->articlesOverviewFactory->create();

        return $comp;
    }


    /*
     * ------------------------------
     * ----- PARTICULAR ARTICLE -----
     * ------------------------------
     */


    public function actionShow($id)
    {
        $article = $this->pageFacade->getArticleAsArray($id);
        if ($article === null) {
            throw new BadRequestException;
        }

        $this->article = ArrayHash::from($article);
    }

    public function renderShow($id)
    {
        $this->template->article = $this->article;
    }

    protected function createComponentArticle()
    {
        $comp = $this-> articleFactory->create($this->article);
        return $comp;
    }

}