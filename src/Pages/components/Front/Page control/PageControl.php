<?php

namespace Pages\Components\Front;

use Nette\Utils\ArrayHash;
use App\Components\BaseControl;
use Pages\Page;

class PageControl extends BaseControl
{
    /** @var Page */
    private $page;

    /** @var bool */
    private $isOnlyIntroShown = false;


    public function __construct(Page $page)
    {
        $this->page = $page;
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