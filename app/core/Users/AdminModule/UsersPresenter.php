<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:31
 */

namespace Users\AdminModule\Presenters;

use Kdyby\Translation\Phrase;
use Users\Authorization\Role;
use Users\Components\Admin\IUsersRolesOverviewControlFactory;
use Users\Components\Admin\IRoleDefinitionControlFactory;
use Users\Components\Admin\IUsersOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Users\Facades\UserFacade;
use Users\Query\RoleQuery;
use Users\Query\UserQuery;

class UsersPresenter extends ProtectedPresenter
{
    /**
     * @var IUsersRolesOverviewControlFactory
     * @inject
     */
    public $usersRolesOverviewControlFactory;

    /**
     * @var IRoleDefinitionControlFactory
     * @inject
     */
    public $roleDefinitionControlFactory;

    /**
     * @var IUsersOverviewControlFactory
     * @inject
     */
    public $usersOverviewControlFactory;

    /**
     * @var UserFacade
     * @inject
     */
    public $userFacade;

    /** @var Role */
    public $role;


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
        $this->role = $this->userFacade->fetchRole((new RoleQuery())->byId($id));
        $this['pageTitle']->setPageTitle(new Phrase('users.roleDefinition.title', ['roleName' => ucfirst($this->role->getName())]));
    }


    public function renderRoleDefinition($id)
    {

    }


    /**
     * @Actions roleDefinition
     */
    protected function createComponentRoleDefinition()
    {
        $comp = $this->roleDefinitionControlFactory
                     ->create($this->role);

        return $comp;
    }


}