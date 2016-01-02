<?php

namespace Dashboard\Presenters;

use Dashboard\Components\PagesOverviewControl;
use Dashboard\Components\IPagesOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Pages\Facades\PageFacade;
use Pages\Query\PageQuery;

class DashboardPresenter extends ProtectedPresenter
{
    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var IPagesOverviewControlFactory
     * @inject
     */
    public $pagesOverviewFactory;


    public function actionDefault()
    {

    }


    public function renderDefault()
    {

    }


    protected function createComponentPublishedPagesOverview()
    {
        $comp = $this->pagesOverviewFactory
                     ->create(
                         (new PageQuery())
                         ->onlyWith(['title, createdAt, publishedAt, isPublished'])
                         ->onlyPublished()
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


}