<?php

namespace Pages\Components\Admin;

use Pages\Exceptions\Logic\InvalidArgumentException;
use App\Components\BaseControl;
use Tags\Facades\TagFacade;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Pages\Page;
use Tags\Query\TagQuery;

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

        $tags = $this->tagFacade
                     ->fetchTags(
                         (new TagQuery())
                          ->indexedByTagId()
                     )->toArray();

        $template->tags = $tags;
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