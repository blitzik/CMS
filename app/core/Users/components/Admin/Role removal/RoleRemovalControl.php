<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 02.04.2016
 */

namespace Users\Components\Admin;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use blitzik\FlashMessages\FlashMessage;
use Kdyby\Translation\Translator;
use Nette\Forms\Controls\SubmitButton;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Users\Authorization\Role;
use Users\Facades\UserFacade;

class RoleRemovalControl extends BaseControl
{
    public $onSuccessRoleRemoval;
    public $onCanceledRemoval;

    /** @var UserFacade */
    private $userFacade;

    /** @var Translator */
    private $translator;

    /** @var Role */
    private $role;


    public function __construct(
        Role $role,
        UserFacade $userFacade,
        Translator $translator
    ) {
        $this->role = $role;
        $this->userFacade = $userFacade;
        $this->translator = $translator;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/roleRemoval.latte');

        $template->role = $this->role;

        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('users.roleRemoval.actions'));

        $form->addSubmit('remove', 'remove')
                ->onClick[] = [$this, 'removeRole'];

        if (!$this->authorizator->isAllowed($this->user, 'user_role', 'remove')) {
            $form['remove']->setDisabled();
        }


        $form->addSubmit('cancel', 'cancel')
                ->onClick[] = [$this, 'cancel'];

        $form->addProtection();

        return $form;
    }


    public function removeRole(SubmitButton $button)
    {
        if (!$this->authorizator->isAllowed($this->user, 'user_role', 'remove')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            $this->redirect('this');
        }

        try {
            $this->userFacade->removeRole($this->role);
            $this->onSuccessRoleRemoval($this->role);

        } catch (ForeignKeyConstraintViolationException $e) {
            $this->flashMessage('users.roleRemoval.messages.roleInUse', FlashMessage::WARNING, ['roleName' => $this->role->getName()]);
            $this->redirect('this');
        }
    }


    public function cancel(SubmitButton $button)
    {
        $this->onCanceledRemoval();
    }

}


interface IRoleRemovalControlFactory
{
    /**
     * @param Role $role
     * @return RoleRemovalControl
     */
    public function create(Role $role);
}