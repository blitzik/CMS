<?php

namespace Tags\Components;

use App\BaseControl;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Arrays;

class TagsOverviewControl extends BaseControl
{
    /** @var ITagControlFactory  */
    private $tagControlFactory;

    /** @var  array */
    private $tags;

    public function __construct(
        array $tags,
        ITagControlFactory $tagControlFactory
    ) {
        $this->tags = Arrays::associate($tags, 'id');;
        $this->tagControlFactory = $tagControlFactory;
    }

    protected function createComponentTag()
    {
        return new Multiplier(function ($tagId) {
            $comp = $this->tagControlFactory->create($this->tags[$tagId]);
            $comp->onTagRemoval[] = [$this, 'onTagRemoval'];

            return $comp;
        });
    }

    public function onTagRemoval($tagId)
    {
        unset($this->tags[$tagId]);
        $this->redrawControl();
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/tagOverview.latte');

        $template->tags = $this->tags;

        $template->render();
    }
}


interface ITagsOverviewControlFactory
{
    /**
     * @param array $tags
     * @return TagsOverviewControl
     */
    public function create(array $tags);
}