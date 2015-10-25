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
    /** @var PageFacade  */
    private $pageFacade;

    /** @var ArticleQuery  */
    private $articleQuery;

    /** @var  string */
    private $title;

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

    protected function createComponentVs()
    {
        $vs = new VisualPaginator();

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

        $resultSet->applyPaginator($paginator, 25);

        $articles = $resultSet->toArray(AbstractQuery::HYDRATE_ARRAY);

        $template->articles = ArrayHash::from($articles);
        $template->title = $this->title;

        $template->render();
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