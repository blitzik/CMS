<?php

/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:31
 */

namespace Users\AdminModule\Presenters;

use Users\Components\Admin\IUsersRolesOverviewControlFactory;
use Users\Components\Admin\IRoleDefinitionControlFactory;
use Users\Components\Admin\IUsersOverviewControlFactory;
use Users\Components\Admin\IRoleRemovalControlFactory;
use Users\Components\Admin\IUserRemovalControlFactory;
use Users\Components\Admin\IUserFormControlFactory;
use Users\Components\Admin\INewRoleControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Users\Components\Admin\NewRoleControl;
use blitzik\FlashMessages\FlashMessage;
use Users\Facades\UserFacade;
use Users\Authorization\Role;
use Kdyby\Translation\Phrase;
use Users\Query\RoleQuery;
use Users\Query\UserQuery;
use Users\User;

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
     * @var IRoleRemovalControlFactory
     * @inject
     */
    public $roleRemovalControlFactory;

    /**
     * @var IUserRemovalControlFactory
     * @inject
     */
    public $userRemovalControlFactory;

    /**
     * @var IUserFormControlFactory
     * @inject
     */
    public $userFormControlFactory;

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

    /** @var User */
    public $pickedUser; // cannot use $user or $userEntity


    /*
     * --------------------------
     * ----- USERS OVERVIEW -----
     * --------------------------
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
     * -------------------------
     * ----- USER DETAIL -------
     * -------------------------
     */
     
    
    public function actionDetail($id)
    {
        $this['pageTitle']->setPageTitle('users.user.detail.title');

        $this->pickedUser = $this->getPickedUser($id);

        $this['userForm']->setEditableUser($this->pickedUser);
    }
    
    
    public function renderDetail($id)
    {
    }


    /**
     * @Actions detail
     */
    protected function createComponentUserForm()
    {
        $comp = $this->userFormControlFactory->create();

        return $comp;
    }
    
    
    /*
     * ------------------------
     * ----- USER REMOVAL -----
     * ------------------------
     */
     
    
    public function actionUserRemove($id)
    {
        $this->pickedUser = $this->getPickedUser($id);
        if ($this->userEntity->getId() === $this->pickedUser->getId()) {
            $this->flashMessage('users.userRemoval.messages.removeYourself', FlashMessage::WARNING);
            $this->redirect('Users:default');
        }

        $this['pageTitle']->setPageTitle('users.userRemoval.title');
    }
    
    
    public function renderUserRemove($id)
    {
    }


    /**
     * @Actions userRemove
     */
    protected function createComponentUserRemoval()
    {
        $comp = $this->userRemovalControlFactory->create($this->pickedUser);

        $comp->onSuccessUserRemoval[] = [$this, 'onSuccessUserRemoval'];
        $comp->onCanceledRemoval[] = [$this, 'onCanceledRemoval'];

        return $comp;
    }


    public function onSuccessUserRemoval(User $user)
    {
        $this->flashMessage('users.userRemoval.messages.success', FlashMessage::SUCCESS, ['username' => $user->getUsername()]);
        $this->redirect('Users:default');
    }


    public function onCanceledRemoval()
    {
        $this->redirect('Users:default');
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
        $this->role = $this->getRole($id);

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


    /*
     * ------------------------
     * ----- ROLE REMOVAL -----
     * ------------------------
     */


    public function actionRoleRemove($id)
    {
        $this['pageTitle']->setPageTitle('users.roleRemoval.title');

        $this->role = $this->getRole($id);
    }


    public function renderRoleRemove($id)
    {
    }


    /**
     * @Actions roleRemove
     */
    protected function createComponentRoleRemoval()
    {
        $comp = $this->roleRemovalControlFactory
                     ->create($this->role);

        $comp->onSuccessRoleRemoval[] = [$this, 'onSuccessRoleRemoval'];
        $comp->onCanceledRemoval[] = [$this, 'onCanceledRoleRemoval'];

        return $comp;
    }


    public function onSuccessRoleRemoval(Role $role)
    {
        $this->flashMessage('users.roleRemoval.messages.success', FlashMessage::SUCCESS, ['roleName' => $role->getName()]);
        $this->redirect('Users:roles');
    }


    public function onCanceledRoleRemoval()
    {
        $this->redirect('Users:roles');
    }



    // --------------------------------------------



    private function getPickedUser($id)
    {
        $user = $this->userFacade
                     ->fetchUser(
                         (new UserQuery())
                          ->byId($id)
                          ->withRoles()
                     );

        if ($user === null) {
            $this->flashMessage('users.messages.userNotFound', FlashMessage::WARNING);
            $this->redirect('Users:default');
        }

        return $user;
    }


    private function getRole($id)
    {
        $role = $this->userFacade
                     ->fetchRole(
                         (new RoleQuery())
                          ->withParent()
                          ->byId($id)
                     );

        if ($role === null) {
            $this->flashMessage('users.messages.roleNotFound', FlashMessage::WARNING);
            $this->redirect('Users:roles');
        }

        return $role;
    }

}