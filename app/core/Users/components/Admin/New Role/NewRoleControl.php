<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 30.03.2016
 */

namespace Users\Components\Admin;

use blitzik\FlashMessages\FlashMessage;
use Users\Exceptions\Runtime\RoleAlreadyExistsException;
use Users\Exceptions\Runtime\RoleMissingException;
use Kdyby\Translation\Translator;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Users\Authorization\Role;
use Users\Facades\UserFacade;

class NewRoleControl extends BaseControl
{
    public $onSuccessRoleCreation;

    /** @var Translator */
    private $translator;

    /** @var UserFacade */
    private $userFacade;


    public function __construct(
        Translator $translator,
        UserFacade $userFacade
    ) {
        $this->translator = $translator;
        $this->userFacade = $userFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/newRole.latte');


        $template->render();
    }


    protected function createComponentNewRoleForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('users.newRole.form'));

        $form->addText('name', 'name.label', null, Role::LENGTH_NAME)
                ->setRequired('name.messages.required');

        $form->addSelect('parent', $this->translator->translate('users.newRole.form.parent.label'))
                ->setTranslator(null)
                ->setPrompt($this->translator->translate('users.newRole.form.parent.prompt'))
                ->setItems($this->prepareRolesForSelect($this->userFacade->findRolesThatAreNotParents()));

        $form->addSubmit('save', 'save.caption');
        
        if (!$this->authorizator->isAllowed($this->user, 'user_role', 'create')) {
            $form['save']->setDisabled();
        }

        $form->onSuccess[] = [$this, 'processNewRole'];

        return $form;
    }


    public function processNewRole(Form $form, $values)
    {
        if (!$this->authorizator->isAllowed($this->user, 'user_role', 'create')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
        }

        try {
            $role = $this->userFacade->createRole((array)$values);
            $this->onSuccessRoleCreation($this, $role);

        } catch (RoleMissingException $rm) {
            $this->flashMessage(
                'users.newRole.form.messages.missingRole',
                FlashMessage::WARNING,
                ['roleName' => $values['name']]
            );

        } catch (RoleAlreadyExistsException $re) {
            $this->flashMessage(
                'users.newRole.form.messages.roleAlreadyExists',
                FlashMessage::WARNING,
                ['roleName' => $values['name']]
            );
        }
    }


    /**
     * @param Role[] $roles
     * @return array [roleID => roleName]
     */
    private function prepareRolesForSelect(array $roles)
    {
        $result = [];
        /** @var Role $role */
        foreach ($roles as $role) {
            $result[$role->getId()] = $role->getName();
        }

        return $result;
    }
}


interface INewRoleControlFactory
{
    /**
     * @return NewRoleControl
     */
    public function create();
}