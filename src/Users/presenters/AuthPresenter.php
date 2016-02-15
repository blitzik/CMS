<?php

namespace Users\Presenters;

use App\Presenters\AppPresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

class AuthPresenter extends AppPresenter
{

    /*
     * --------------------
     * ----- LOGIN --------
     * --------------------
     */


    public function actionLogin()
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect(':Dashboard:Dashboard:default');
        }
    }


    public function renderLogin()
    {
        
    }


    protected function createComponentLoginForm()
    {
        $form = new Form;

        $form->addText('email', 'E-mailová adresa')
                ->setAttribute('placeholder', 'Emailová adresa')
                ->setRequired('Vyplňte E-mailovou adresu')
                ->addRule(Form::EMAIL, 'Vaše E-mailová adresa nemá správný tvar');

        $form->addPassword('password', 'Heslo')
                ->setAttribute('placeholder', 'Heslo')
                ->setRequired('Vyplňte své heslo');

        $form->addSubmit('login', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'processLogin'];

        return $form;
    }


    public function processLogin(Form $form, $values)
    {
        try {
            $this->user->login($values->email, $values->password);

            $this->redirect(':Dashboard:Dashboard:default');

        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }


    /*
     * --------------------
     * ----- LOGOUT -------
     * --------------------
     */


    public function actionLogout()
    {
        $this->user->logout();
        $this->redirect('Auth:login');
    }


    public function renderLogout()
    {

    }

}