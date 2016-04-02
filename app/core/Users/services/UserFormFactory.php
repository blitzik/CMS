<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 02.04.2016
 */

namespace Users\Factories;

use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Users\Authorization\Role;
use Users\Facades\UserFacade;
use Users\Query\RoleQuery;
use Nette\Object;
use Users\User;

class UserFormFactory extends Object
{
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


    /**
     * @param User|null $user
     * @return Form
     */
    public function create(User $user = null)
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('users.user.form'));

        $form->addText('username', 'username.label')
                ->setRequired('username.messages.required');

        $form->addText('email', 'email.label')
                ->setRequired('email.messages.required');

        $form->addText('first_name', 'first_name.label');

        $form->addText('last_name', 'last_name.label');

        $form->addSelect('role', $this->translator->translate('users.user.form.role.label'), $this->prepareRolesForSelect())
                ->setTranslator(null);

        $form->addSubmit('save', 'save.caption');

        if ($user !== null) {
            $this->fillForm($form, $user);
        }

        return $form;
    }


    private function prepareRolesForSelect()
    {
        $roles = $this->userFacade
                      ->fetchRoles(
                          (new RoleQuery())
                      );

        $result = [];
        /** @var Role $role */
        foreach ($roles as $role) {
            $result[$role->getId()] = $role->getName();
        }

        return $result;
    }


    private function fillForm(Form $form, User $user)
    {
        $form['username']->setDefaultValue($user->getUsername());
        $form['email']->setDefaultValue($user->getEmail());
        $form['first_name']->setDefaultValue($user->getFirstName());
        $form['last_name']->setDefaultValue($user->getLastName());

        foreach ($user->getRoles() as $roleID => $role) { // todo
            $form['role']->setDefaultValue($roleID);
        }
    }
}