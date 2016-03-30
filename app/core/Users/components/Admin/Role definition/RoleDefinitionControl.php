<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 30.03.2016
 */

namespace Users\Components\Admin;

use Doctrine\DBAL\DBALException;
use Users\Authorization\AccessDefinition;
use blitzik\FlashMessages\FlashMessage;
use Users\Query\AccessDefinitionQuery;
use Users\Authorization\Permission;
use Users\Query\PermissionQuery;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Users\Authorization\Role;
use Users\Facades\UserFacade;

class RoleDefinitionControl extends BaseControl
{
    /** @var UserFacade */
    private $userFacade;

    /** @var Role */
    private $role;


    public function __construct(
        Role $role,
        UserFacade $userFacade
    ) {
        $this->role = $role;
        $this->userFacade = $userFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/roleDefinition.latte');

        $resultSet = $this->userFacade
                          ->fetchAccessDefinitions(
                              new AccessDefinitionQuery()
                          );

        $template->accessDefinitions = $this->sortAccessDefinitions($resultSet->toArray());

        $permissionsResultSet = $this->userFacade
                                     ->fetchPermissions(
                                         (new PermissionQuery())
                                         ->byRole($this->role->getId())
                                     );

        $template->permissions = $this->sortRolePermissions($permissionsResultSet->toArray());

        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form;

        $form->addSubmit('save', 'Save');

        $form->onSuccess[] = [$this, 'processPrivileges'];

        return $form;
    }


    public function processPrivileges(Form $form)
    {
        $values = $form->getHttpData();
        unset($values['save'], $values['do']);

        try {
            $this->userFacade->savePermissionDefinitions($this->role, $values);
            $this->flashMessage('users.roleDefinition.messages.success', FlashMessage::SUCCESS);
            $this->redirect('this');
            
        } catch (DBALException $e) {
            $this->flashMessage('users.roleDefinition.messages.success', FlashMessage::WARNING);
        }
    }


    private function sortAccessDefinitions(array $definitions)
    {
        $result = [];
        /** @var AccessDefinition $definition */
        foreach ($definitions as $definition) {
            $result[$definition->getResourceName()][$definition->getPrivilegeId()] = $definition;
        }

        return $result;
    }


    private function sortRolePermissions(array $permissions)
    {
        $result = [];
        /** @var Permission $permission */
        foreach ($permissions as $permission) {
            $result[$permission->getResourceName()][$permission->getPrivilegeId()] = $permission;
        }

        return $result;
    }
}


interface IRoleDefinitionControlFactory
{
    /**
     * @param Role $role
     * @return RoleDefinitionControl
     */
    public function create(Role $role);
}