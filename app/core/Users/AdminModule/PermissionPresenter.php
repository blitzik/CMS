<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 11:33
 */

namespace Users\AdminModule\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;

class PermissionPresenter extends ProtectedPresenter
{
    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('usersPermissions.title');
    }


    public function renderDefault()
    {
    }
}