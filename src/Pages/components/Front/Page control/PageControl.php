<?php

namespace Pages\Components\Front;

use Kdyby\Translation\Translator;
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

    /** @var Translator */
    private $translator;


    public function __construct(Page $page, Translator $translator)
    {
        $this->page = $page;
        $this->translator = $translator;
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

        $template->isOnlyIntroShown = $this->isOnlyIntroShown;
        $template->commentsCount = $this->commentsCount;

        $template->translate = function ($string, $count = null) {
            return $this->translator->translate($string, $count, [], null, $this->page->getLocaleCode());
        };

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