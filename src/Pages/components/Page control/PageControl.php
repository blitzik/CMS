<?php

namespace Pages\Components;

use Nette\Utils\ArrayHash;
use App\BaseControl;

class PageControl extends BaseControl
{
    /** @var ArrayHash  */
    private $page;

    /** @var bool  */
    private $isOnlyIntroShown = false;

    public function __construct(ArrayHash $page)
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
     * @param ArrayHash $page
     * @return PageControl
     */
    public function create(ArrayHash $page);
}