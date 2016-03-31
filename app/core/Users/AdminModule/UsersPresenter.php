<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:31
 */

namespace Users\AdminModule\Presenters;

use blitzik\FlashMessages\FlashMessage;
use Users\Components\Admin\IUsersRolesOverviewControlFactory;
use Users\Components\Admin\IRoleDefinitionControlFactory;
use Users\Components\Admin\IUsersOverviewControlFactory;
use Users\Components\Admin\INewRoleControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Users\Components\Admin\NewRoleControl;
use Users\Facades\UserFacade;
use Users\Authorization\Role;
use Kdyby\Translation\Phrase;
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
     * @var INewRoleControlFactory
     * @inject
     */
    public $newRoleControlFactory;

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
     * --------------------
     * ----- NEW ROLE -----
     * --------------------
     */
     
    
    public function actionNewRole()
    {
        $this['pageTitle']->setPageTitle('users.newRole.title');
    }
    
    
    public function renderNewRole()
    {
        
    }


    /**
     * @Actions newRole
     */
    protected function createComponentNewRole()
    {
        $comp = $this->newRoleControlFactory->create();
        $comp->onSuccessRoleCreation[] = [$this, 'onSuccessRoleCreation'];

        return $comp;
    }


    public function onSuccessRoleCreation(NewRoleControl $control, Role $role)
    {
        $this->flashMessage('users.newRole.form.messages.success', FlashMessage::SUCCESS, ['roleName' => $role->getName()]);
        $this->redirect('Users:roles');
    }
    

    /*
     * ---------------------------
     * ----- ROLE DEFINITION -----
     * ---------------------------
     */


    public function actionRoleDefinition($id)
    {
        $this->role = $this->userFacade
                           ->fetchRole(
                               (new RoleQuery())
                               ->withParent()
                               ->byId($id)
                           );

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