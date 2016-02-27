<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 26.02.2016
 */

namespace Pages\FrontModule\Presenters;

use Nette\Application\UI\Multiplier;
use Pages\Components\Front\IPageControlFactory;
use Pages\Components\Front\IPagesSearchControlFactory;
use App\FrontModule\Presenters\BasePresenter;
use Pages\Facades\PageFacade;
use Tags\Facades\TagFacade;
use Tags\Query\TagQuery;
use Tags\Tag;

class SearchPresenter extends BasePresenter
{
    /**
     * @var IPagesSearchControlFactory
     * @inject
     */
    public $tagsOverviewFactory;

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

    /** @var Tag[] */
    public $tags;

    /** @var array */
    public $pages;


    public function actionTag($tags)
    {
        $this['pageTitle']->setPageTitle('Vyhledávání podle štítku');

        $this->tags = $this->tagFacade
                           ->fetchTags(
                               (new TagQuery())
                                ->orderByName()
                                ->indexedByTagId()
                           )->toArray();

        $wantedTags = explode('-', $tags);
        $this['pagesSearch']->setSelectedTags($wantedTags);

        $this->pages = $this->pageFacade->searchByTags(array_intersect(array_keys($this->tags), $wantedTags));
    }


    public function renderTag($tags)
    {
        $this->template->pages = $this->pages;
        $this->template->wantedTags = $tags;
    }


    /**
     * @Actions tag
     */
    protected function createComponentPagesSearch()
    {
        $comp = $this->tagsOverviewFactory->create($this->tags);
        return $comp;
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