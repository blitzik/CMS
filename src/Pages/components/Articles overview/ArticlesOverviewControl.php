<?php

namespace Pages\Components;

use App\BaseControl;
use blitzik\IPaginatorFactory;
use Doctrine\ORM\AbstractQuery;
use Nette\Application\UI\Multiplier;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Nette\Utils\Paginator;
use Nette\Utils\Validators;
use Pages\Facades\PageFacade;
use Pages\Query\ArticleQuery;

class ArticlesOverviewControl extends BaseControl
{
    /** @var array */
    public $onPaginate = [];

    /** @var PageFacade  */
    private $pageFacade;

    /** @var IArticleControlFactory  */
    private $articleControlFactory;

    /** @var  array */
    private $articles;

    /** @var IPaginatorFactory  */
    private $paginatorFactory;

    /** @var  int */
    private $articlesPerPage;

    public function __construct(
        PageFacade $pageFacade,
        IArticleControlFactory $articleControlFactory,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->pageFacade = $pageFacade;
        $this->articleControlFactory = $articleControlFactory;
        $this->paginatorFactory = $paginatorFactory;
    }

    public function setArticlesPerPage($articlesPerPage)
    {
        Validators::assert($articlesPerPage, 'numericint:1..');

        $this->articlesPerPage = $articlesPerPage;
    }

    protected function createComponentVs()
    {
        $comp = $this->paginatorFactory->create();
        $comp->hideCounter();
        $comp->notAjaxified();

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

        $this->onPaginate($paginator);

        $resultSet = $this->pageFacade
                          ->fetchArticles(
                              (new ArticleQuery())
                              ->forOverview()
                              ->withTags()
                              ->onlyPublished()
                              ->orderByPublishedAt('DESC')
                          );

        $resultSet->applyPaginator($paginator, $this->articlesPerPage);

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