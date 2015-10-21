<?php

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

class SignPresenter extends BasePresenter
{
    public function actionLogin()
    {

    }

    public function renderLogin()
    {

    }

    protected function createComponentLoginForm()
    {
        $form = new Form;

        $form->addText('email', 'E-mail')
                ->setRequired('Type your E-mail')
                ->addRule(Form::EMAIL, 'Your E-mail address does NOT ahve correct format.');

        $form->addPassword('password', 'Password')
                ->setRequired('Type your password');

        $form->addSubmit('signIn', 'Sign in');

        $form->onSuccess[] = [$this, 'onSignIn'];

        return $form;
    }

    public function onSignIn(Form $form, $values)
    {
        try {
            $this->user->login($values->email, $values->password);
            $this->user->setExpiration('+14 days', false);

            $this->redirect(':Dashboard:Dashboard:default');

        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}