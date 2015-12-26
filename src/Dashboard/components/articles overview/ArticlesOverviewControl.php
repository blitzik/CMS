<?php

namespace Dashboard\Components;

use App\BaseControl;
use blitzik\VisualPaginator;
use Doctrine\ORM\AbstractQuery;
use Nette\Utils\ArrayHash;
use Nette\Utils\Paginator;
use Pages\Facades\PageFacade;
use Pages\Query\ArticleQuery;

class ArticlesOverviewControl extends BaseControl
{
    /** @var array  */
    public $onToggleVisibility = [];


    /** @var PageFacade  */
    private $pageFacade;

    /** @var ArticleQuery  */
    private $articleQuery;

    /** @var  string */
    private $title;

    /** @var  string */
    private $icon;

    /** @var  int */
    private $articlesCount = 10;

    public function __construct(
        ArticleQuery $articleQuery,
        PageFacade $pageFacade
    ) {
        $this->pageFacade = $pageFacade;
        $this->articleQuery = $articleQuery;
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

    public function setArticlesPerPage($articlesCount)
    {
        $this->articlesCount = $articlesCount;
    }

    protected function createComponentVs()
    {
        $vs = new VisualPaginator();
        $vs->notAjaxified();
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
                          ->fetchArticles($this->articleQuery);

        /** @var Paginator $paginator */
        $paginator = $this['vs']->getPaginator();

        $resultSet->applyPaginator($paginator, $this->articlesCount);

        $articles = $resultSet->toArray(AbstractQuery::HYDRATE_ARRAY);

        $template->articles = ArrayHash::from($articles);
        $template->title = $this->title;
        $template->icon = $this->icon;



        $template->render();
    }

    public function handlePublishArticle($id)
    {
        $this->pageFacade->publishArticle($id);

        $this->flashMessage('Článek byl úspěšně publikován.', 'success');
        $this->onToggleVisibility($this);
    }

    public function handleHideArticle($id)
    {
        $this->pageFacade->hideArticle($id);

        $this->flashMessage('Článek již není veřejné přístupný.', 'success');
        $this->onToggleVisibility($this);
    }
}


interface IArticlesOverviewControlFactory
{
    /**
     * @param ArticleQuery $articleQuery
     * @return ArticlesOverviewControl
     */
    public function create(ArticleQuery $articleQuery);
}