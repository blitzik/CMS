<?php

namespace App\Presenters;

use App\Components\IFlashMessagesControlFactory;
use App\Components\IMetaTagsControlFactory;
use App\Components\IPageTitleControlFactory;
use Nette\Application\UI\Presenter;
use Options\Facades\OptionFacade;
use Users\User;

abstract class AppPresenter extends Presenter
{
    /**
     * @var IFlashMessagesControlFactory
     * @inject
     */
    public $flashMessagesFactory;

    /**
     * @var IMetaTagsControlFactory
     * @inject
     */
    public $metaTagsControlFactory;

    /**
     * @var IPageTitleControlFactory
     * @inject
     */
    public $pageTitleFactory;

    /**
     * @var OptionFacade
     * @inject
     */
    public $optionFacade;

    /** @var  User */
    protected $userEntity;

    /** @var  array */
    protected $options;


    protected function startup()
    {
        parent::startup();

        $this->userEntity = $this->user->getIdentity();
        $this->options = $this->optionFacade->loadOptions();
    }


    protected function createComponentFlashMessages()
    {
        $comp = $this->flashMessagesFactory->create();

        return $comp;
    }


    protected function createComponentMetas()
    {
        $comp = $this->metaTagsControlFactory->create();

        return $comp;
    }


    protected function createComponentPageTitle()
    {
        $comp = $this->pageTitleFactory->create($this->options->blog_title);

        return $comp;
    }


    /**
     * Common render method.
     * @return void
     */
    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->userEntity = $this->userEntity;
    }


}