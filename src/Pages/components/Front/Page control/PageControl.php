<?php

namespace Pages\Components\Front;

use Nette\Utils\ArrayHash;
use App\Components\BaseControl;
use Pages\Page;

class PageControl extends BaseControl
{
    /** @var bool */
    private $isOnlyIntroShown = false;

    /** @var Page */
    private $page;

    /** @var int */
    private $commentsCount = 0;


    public function __construct(Page $page)
    {
        $this->page = $page;
    }


    /**
     * @param int $commentsCount
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;
    }


    public function onlyIntro()
    {
        $this->isOnlyIntroShown = true;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/page.latte');

        $template->page = $this->page;
        $template->month = $this->page->publishedAt->format('n');

        $template->isOnlyIntroShown = $this->isOnlyIntroShown;
        $template->commentsCount = $this->commentsCount;

        $template->render();
    }
}


interface IPageControlFactory
{
    /**
     * @param Page $page
     * @return PageControl
     */
    public function create(Page $page);
}