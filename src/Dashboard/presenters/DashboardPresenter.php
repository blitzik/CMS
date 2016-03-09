<?php

namespace Dashboard\Presenters;

use Dashboard\Components\ILogOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Log\Query\LogQuery;

class DashboardPresenter extends ProtectedPresenter
{
    /**
     * @var ILogOverviewControlFactory
     * @inject
     */
    public $logOverviewFactory;


    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('dashboard.title');


    }


    public function renderDefault()
    {
    }


    protected function createComponentLogOverview()
    {
        $comp = $this->logOverviewFactory
                     ->create(
                         (new LogQuery())
                          ->descendingOrder()
                     );

        return $comp;
    }
}