<?php

namespace Dashboard\Presenters;

use Dashboard\Components\ArticlesOverviewControl;
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
                         ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                         ->onlyPublished()
                         ->orderByPublishedAt('DESC')
                     );

        $comp->setTitle('Publikované články');
        $comp->setPrependTitleIcon('eye');

        $comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }

    protected function createComponentWaitingArticlesOverview()
    {
        $comp = $this->articlesOverviewFactory
                     ->create(
                         (new ArticleQuery())
                         ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                         ->waitingForBeingPublished()
                     );

        $comp->setTitle('Články čekající na zveřejnění');
        $comp->setPrependTitleIcon('hourglass-half');

        $comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }

    protected function createComponentUnpublishedArticlesOverview()
    {
        $comp = $this->articlesOverviewFactory
                     ->create(
                         (new ArticleQuery())
                         ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                         ->notPublished()
                         ->orderByPublishedAt('DESC')
                     );

        $comp->setTitle('Nepublikované články');
        $comp->setPrependTitleIcon('eye-slash');

        $comp->onToggleVisibility[] = [$this, 'onToggleVisibility'];

        return $comp;
    }

    public function onToggleVisibility(ArticlesOverviewControl $control)
    {
        if ($this->isAjax()) {
            $control->redrawControl('table');
            $this->redrawControl('articlesTables');
        } else {
            $this->redirect('this#'.$control->getUniqueId());
        }
    }


}