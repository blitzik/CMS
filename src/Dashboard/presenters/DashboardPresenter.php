<?php

namespace Dashboard\Presenters;

use Dashboard\Components\IArticlesOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Pages\Facades\PageFacade;
use Pages\Query\ArticleQuery;

class DashboardPresenter extends ProtectedPresenter
{
    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var IArticlesOverviewControlFactory
     * @inject
     */
    public $articlesOverviewFactory;

    public function actionDefault()
    {

    }

    public function renderDefault()
    {

    }

    protected function createComponentPublishedArticlesOverview()
    {
        $comp = $this->articlesOverviewFactory
                     ->create(
                         (new ArticleQuery())
                         ->onlyWith(['title, createdAt, publishedAt'])
                         ->onlyPublished()
                     );

        $comp->setTitle('Publikované články');

        return $comp;
    }

    protected function createComponentWaitingArticlesOverview()
    {
        $comp = $this->articlesOverviewFactory
                     ->create(
                         (new ArticleQuery())
                         ->onlyWith(['title, createdAt, publishedAt'])
                         ->waitingForBeingPublished()
                     );

        $comp->setTitle('Články čekající na zveřejnění');

        return $comp;
    }

    protected function createComponentUnpublishedArticlesOverview()
    {
        $comp = $this->articlesOverviewFactory
                     ->create(
                         (new ArticleQuery())
                         ->onlyWith(['title, createdAt, publishedAt'])
                         ->notPublished()
                     );

        $comp->setTitle('Nepublikované články');

        return $comp;
    }


}