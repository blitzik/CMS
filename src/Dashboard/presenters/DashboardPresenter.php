<?php

namespace Dashboard\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;

class DashboardPresenter extends ProtectedPresenter
{
    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('dashboard.title');
    }


    public function renderDefault()
    {
    }
}