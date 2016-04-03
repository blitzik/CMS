<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 01.04.2016
 */

namespace Users\Components\Admin;

use App\ValidationObjects\ValidationError;
use blitzik\FlashMessages\FlashMessage;
use Users\Factories\UserFormFactory;
use Kdyby\Translation\Translator;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Users\Facades\UserFacade;
use Users\User;

class UserFormControl extends BaseControl
{
    /** @var UserFormFactory */
    private $userFormFactory;

    /** @var UserFacade */
    private $userFacade;

    /** @var Translator */
    private $translator;

    /** @var User */
    private $pickedUser;


    public function __construct(
        UserFormFactory $userFormFactory,
        UserFacade $userFacade,
        Translator $translator
    ) {
        $this->userFormFactory = $userFormFactory;
        $this->userFacade = $userFacade;
        $this->translator = $translator;
    }


    public function setEditableUser(User $userEntity)
    {
        $this->pickedUser = $userEntity;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/userForm.latte');

        $template->pickedUser = $this->pickedUser;

        $template->render();
    }


    protected function createComponentForm()
    {
        $form = $this->userFormFactory->create($this->pickedUser);

        $form['username']->setOmitted()
                         ->getControlPrototype()->readOnly = 'readOnly';

        $form['email']->setOmitted()
                      ->getControlPrototype()->readOnly = 'readOnly';

        $form->onSuccess[] = [$this, 'processUser'];
        
        if (!$this->authorizator->isAllowed($this->user, 'user', 'edit')) {
            $form['save']->setDisabled();
        }

        $form->addProtection();

        return $form;
    }


    public function processUser(Form $form, $values)
    {
        if (!$this->authorizator->isAllowed($this->user, 'user', 'edit')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
        }

        $validationObject = $this->userFacade->saveUser((array)$values, $this->pickedUser);
        if ($validationObject->isValid()) {
            $this->flashMessage('users.user.form.messages.success', FlashMessage::SUCCESS);
            $this->redirect('this');
        }
        
        /** @var ValidationError $error */
        foreach ($validationObject->getErrors() as $error) {
            $this->flashMessage($error->getMessage(), $error->getType());
        }
    }
}


interface IUserFormControlFactory
{
    /**
     * @return UserFormControl
     */
    public function create();
}