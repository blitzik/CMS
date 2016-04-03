<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 03.04.2016
 */

namespace Users\Components\Admin;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use blitzik\FlashMessages\FlashMessage;
use Kdyby\Translation\Translator;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Users\Facades\UserFacade;
use Users\User;

class UserRemovalControl extends BaseControl
{
    public $onSuccessUserRemoval;
    public $onCanceledRemoval;

    /** @var UserFacade */
    private $userFacade;

    /** @var User */
    private $pickedUser;

    /** @var Translator */
    private $translator;


    public function __construct(
        User $user,
        UserFacade $userFacade,
        Translator $translator
    ) {
        $this->userFacade = $userFacade;
        $this->pickedUser = $user;
        $this->translator = $translator;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/userRemoval.latte');

        $template->pickedUser = $this->pickedUser;

        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('users.userRemoval.actions'));

        $form->addSubmit('remove', 'remove')
                ->onClick[] = [$this, 'removeRole'];

        if (!$this->authorizator->isAllowed($this->user, 'user', 'remove')) {
            $form['remove']->setDisabled();
        }

        $form->addSubmit('cancel', 'cancel')
                ->onClick[] = [$this, 'cancel'];
        
        $form->addProtection();

        return $form;
    }


    public function removeRole(SubmitButton $button)
    {
        if (!$this->authorizator->isAllowed($this->user, 'user', 'remove')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            $this->redirect('this');
        }

        try {
            $this->userFacade->removeUser($this->pickedUser);
            $this->onSuccessUserRemoval($this->pickedUser);

        } catch (ForeignKeyConstraintViolationException $e) {
            $this->flashMessage('users.userRemoval.messages.cantBeRemoved', FlashMessage::WARNING, ['username' => $this->pickedUser->getUsername()]);
            $this->redirect('this');
        }
    }


    public function cancel(SubmitButton $button)
    {
        $this->onCanceledRemoval();
    }
}


interface IUserRemovalControlFactory
{
    /**
     * @param User $user
     * @return UserRemovalControl
     */
    public function create(User $user);
}