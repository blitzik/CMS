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
        $this['pageTitle']->setPageTitle('Vyhledávání podle štítku');

        $this->pages = $this->pageFacade->searchByTag($internal_id);
    }


    public function renderTag($internal_id)
    {
        $this->template->pages = $this->pages;
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