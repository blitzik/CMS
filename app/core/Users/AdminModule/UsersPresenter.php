<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:31
 */

namespace Users\AdminModule\Presenters;

use Users\Components\Admin\IUsersRolesOverviewControlFactory;
use Users\Components\Admin\IUsersOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Users\Query\RoleQuery;
use Users\Query\UserQuery;

class UsersPresenter extends ProtectedPresenter
{
    /**
     * @var IUsersOverviewControlFactory
     * @inject
     */
    public $usersOverviewControlFactory;

    /**
     * @var IUsersRolesOverviewControlFactory
     * @inject
     */
    public $usersRolesOverviewControlFactory;


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


    /**
     * @Actions default
     */
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
     * ----- DETAIL -------
     * --------------------
     */
     
    
    public function actionDetail($id)
    {
        $this['pageTitle']->setPageTitle('users.detail.title');
    }
    
    
    public function renderDetail($id)
    {
        
    }


    /*
     * -----------------------------
     * ----- ROLES OVERVIEW --------
     * -----------------------------
     */


    public function actionRoles()
    {
        $this['pageTitle']->setPageTitle('users.roles.title');
    }


    public function renderRoles()
    {
    }


    /**
     * @Actions roles
     */
    protected function createComponentRolesOverview()
    {
        $comp = $this->usersRolesOverviewControlFactory
                     ->create(
                         (new RoleQuery())
                         ->withParent()
                     );

        return $comp;
    }


    /*
     * ---------------------------
     * ----- ROLE DEFINITION -----
     * ---------------------------
     */


    public function actionRoleDefinition($id)
    {
        $this['pageTitle']->setPageTitle('users.roleDefinition.title');
    }


    public function renderRoleDefinition($id)
    {

    }


}