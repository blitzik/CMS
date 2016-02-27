<?php

namespace Users\Presenters;

use App\Presenters\AppPresenter;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\AuthenticationException;

class AuthPresenter extends AppPresenter
{
    /** @persistent */
    public $locale;

    /**
     * @var ITranslator
     * @inject
     */
    public $translator;


    protected function startup()
    {
        parent::startup();

        if ($this->session->hasSection('cms_localization')) {
            $localizationSection = $this->session->getSection('cms_localization');
            if ($localizationSection->locale !== null) {
                $this->locale = $localizationSection->locale;
            }
        }
    }


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


    /**
     * @Actions login
     */
    protected function createComponentLoginForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('users.login.form'));

        $form->addText('email', 'email.label')
                ->setAttribute('placeholder', 'email.placeholder')
                ->setAttribute('title', $this->translator->translate('users.login.form.email.title'))
                ->setRequired('email.messages.required')
                ->addRule(Form::EMAIL, 'email.messages.wrongFormat');

        $form->addPassword('password', 'password.label')
                ->setAttribute('placeholder', 'password.placeholder')
                ->setAttribute('title', $this->translator->translate('users.login.form.password.title'))
                ->setRequired('password.messages.required');

        $form->addSubmit('login', 'login.caption');

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