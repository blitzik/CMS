<?php

namespace Pages\Components;

use App\BaseControl;
use blitzik\IPaginatorFactory;
use Doctrine\ORM\AbstractQuery;
use Nette\Application\UI\Multiplier;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Nette\Utils\Paginator;
use Pages\Facades\PageFacade;
use Pages\Query\ArticleQuery;

class ArticlesOverviewControl extends BaseControl
{
    /** @var PageFacade  */
    private $pageFacade;

    /** @var IArticleControlFactory  */
    private $articleControlFactory;

    /** @var  array */
    private $articles;

    /** @var IPaginatorFactory  */
    private $paginatorFactory;

    public function __construct(
        PageFacade $pageFacade,
        IArticleControlFactory $articleControlFactory,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->pageFacade = $pageFacade;
        $this->articleControlFactory = $articleControlFactory;
        $this->paginatorFactory = $paginatorFactory;
    }

    protected function createComponentVs()
    {
        $comp = $this->paginatorFactory->create();
        $comp->hideCounter();

        return $comp;
    }

    protected function createComponentArticle()
    {
        return new Multiplier(function ($articleId) {
            $comp = $this->articleControlFactory->create($this->articles[$articleId]);
            $comp->onlyIntro();

            return $comp;
        });
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/overview.latte');

        /** @var Paginator $paginator */
        $paginator = $this['vs']->getPaginator();

        $resultSet = $this->pageFacade
                          ->fetchArticles(
                              (new ArticleQuery())
                              ->forOverview()
                              ->withTags()
                              ->onlyPublished()
                              ->orderByPublishedAt('DESC')
                          );

        $resultSet->applyPaginator($paginator, 15);

        $this->articles = Arrays::associate($resultSet->toArray(AbstractQuery::HYDRATE_ARRAY), 'id');
        $this->articles = ArrayHash::from($this->articles);

        $template->articles = $this->articles;
        $template->articlesCount = count($this->articles);

        $template->render();
    }
}


interface IArticlesOverviewControlFactory
{
    /**
     * @return ArticlesOverviewControl
     */
    public function create();
}