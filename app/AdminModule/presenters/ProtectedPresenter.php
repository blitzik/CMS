<?php

namespace App\AdminModule\Presenters;

use App\Components\ILocaleSwitcherControlFactory;
use App\Components\IPageTitleControlFactory;
use App\Presenters\AppPresenter;
use blitzik\FlashMessages\TFlashMessages;
use Nette\Localization\ITranslator;

abstract class ProtectedPresenter extends AppPresenter
{
    use TFlashMessages;

    /** @persistent */
    public $locale;

    /**
     * @var ILocaleSwitcherControlFactory
     * @inject
     */
    public $localeSwitcherFactory;

    /**
     * @var ITranslator
     * @inject
     */
    public $translator;

    /**
     * @var IPageTitleControlFactory
     * @inject
     */
    public $pageTitleFactory;


    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Přihlašte se prosím.');
            $this->redirect(':Users:Auth:login');
        }
    }


    /**
     * Finds layout template file name.
     * @return string
     * @internal
     */
    public function findLayoutTemplateFile()
    {
        return __DIR__ . '/templates/@layout.latte';
    }


    protected function createComponentPageTitle()
    {
        $comp = $this->pageTitleFactory
                     ->create('Blog - Administrace');

        return $comp;
    }


    protected function createComponentLocaleSwitcher()
    {
        $comp = $this->localeSwitcherFactory->create();
        $comp->setLocale($this->locale);

        return $comp;
    }
}