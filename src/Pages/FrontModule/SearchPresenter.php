<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 26.02.2016
 */

namespace Pages\FrontModule\Presenters;

use blitzik\FlashMessages\FlashMessage;
use Pages\Components\Front\IPageControlFactory;
use App\FrontModule\Presenters\BasePresenter;
use Nette\Application\UI\Multiplier;
use Pages\Facades\PageFacade;
use Tags\Facades\TagFacade;

class SearchPresenter extends BasePresenter
{
    /**
     * @var IPageControlFactory
     * @inject
     */
    public $pageControlFactory;

    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var TagFacade
     * @inject
     */
    public $tagFacade;

    /** @var array */
    public $pages;


    public function actionTag($internal_id)
    {
        if ($internal_id === null) {
            //$this->flashMessage('', FlashMessage::WARNING);
            $this->redirect(':Pages:Front:Page:default');
        }

        $this['pageTitle']->setPageTitle('Vyhledávání podle štítku');

        $this->pages = $this->pageFacade->searchByTag($internal_id);
    }


    public function renderTag($internal_id)
    {
        $this->template->pages = $this->pages;

        $tag = null;
        if ($internal_id !== null) {
            $tag = $this->tagFacade->getById($internal_id);
        }

        $this->template->tag = $tag;
    }


    protected function createComponentPage()
    {
        return new Multiplier(function ($pageID) {
            $comp = $this->pageControlFactory->create($this->pages[$pageID][0]);
            $comp->setCommentsCount($this->pages[$pageID]['commentsCount']);
            $comp->onlyIntro();

            return $comp;
        });
    }
}