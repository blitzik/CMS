<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:31
 */

namespace Users\AdminModule\Presenters;

use Users\Components\Admin\IUsersOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Users\Query\UserQuery;

class UsersPresenter extends ProtectedPresenter
{
    /**
     * @var IUsersOverviewControlFactory
     * @inject
     */
    public $usersOverviewControlFactory;


    /*
     * --------------------
     * ----- OVERVIEW -----
     * --------------------
     */


    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('users.overview.title');
    }


    public function renderDefault()
    {
    }


    protected function createComponentUsersOverview()
    {
        $comp = $this->usersOverviewControlFactory
                     ->create(
                         (new UserQuery())
                         ->withRoles()
                     );


        return $comp;
    }


    /*
     * --------------------
     * ----- ROLES --------
     * --------------------
     */


    public function actionRoles()
    {
        $this['pageTitle']->setPageTitle('usersPermissions.title');
    }


    public function renderRoles()
    {
    }

}