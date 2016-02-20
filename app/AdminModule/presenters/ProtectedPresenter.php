<?php

namespace App\AdminModule\Presenters;

use App\Components\IPageTitleControlFactory;
use App\Presenters\AppPresenter;

abstract class ProtectedPresenter extends AppPresenter
{
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
}