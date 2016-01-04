<?php

namespace Pages\Components;

use App\Exceptions\LogicExceptions\InvalidArgumentException;
use Tags\Facades\TagFacade;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use App\Components\BaseControl;
use Pages\Page;

class PageTagsPickingControl extends BaseControl
{
    /** @var TagFacade  */
    private $tagFacade;

    /** @var Page|null  */
    private $page;


    public function __construct(
        $page,
        TagFacade $tagFacade
    ) {
        if ($page !== null and !$page instanceof Page) {
            throw new InvalidArgumentException('Only instances of ' .Page::class. ' or NULL is allowed');
        }

        $this->page = $page;
        $this->tagFacade = $tagFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/pageTagsPicking.latte');

        $template->tags = ArrayHash::from(Arrays::associate($this->tagFacade->findAllTags(), 'id'));
        $template->pageTags = isset($this->page) ? $this->page->getTags() : [];

        $template->render();
    }
}


interface IPageTagsPickingControlFactory
{
    /**
     * @param Page|null $page
     * @return PageTagsPickingControl
     */
    public function create($page);
}