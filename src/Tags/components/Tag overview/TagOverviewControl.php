<?php

namespace Tags\Components;

use App\BaseControl;
use Nette\Application\UI\Multiplier;
use Tags\Facades\TagFacade;
use Tracy\Debugger;

class TagsOverviewControl extends BaseControl
{
    /** @var array  */
    public $onMissingTag = [];

    /** @var ITagControlFactory  */
    private $tagControlFactory;

    /** @var TagFacade  */
    private $tagFacade;

    /** @var  array */
    private $tags = [];

    public function __construct(
        TagFacade $tagFacade,
        ITagControlFactory $tagControlFactory
    ) {
        $this->tagControlFactory = $tagControlFactory;
        $this->tagFacade = $tagFacade;
    }

    protected function createComponentTag()
    {
        return new Multiplier(function ($tagId) {
            if (empty($this->tags)) {
                // if processing "handle" method, $this->tags is always empty array
                // because this factory is invoked before render method
                $tag = $this->tagFacade->getTagAsArray($tagId);
                if (!isset($tag[0])) { // trying to request non-existing tag
                    $this->onMissingTag($this); // there is happening redirect
                }
                $this->tags[$tagId] = $tag = $tag[0];
            } else { // common request
                $tag = $this->tags[$tagId];
            }

            $comp = $this->tagControlFactory->create($tag);

            $comp->onTagRemoval[] = [$this, 'onTagRemoval'];
            $comp->onTagRemovalFailure[] = [$this, 'onTagRemovalFailure'];

            return $comp;
        });
    }

    public function onTagRemoval($tagId)
    {
        // signal removeTag has been invoked and therefore there is only one
        // item in $this->tags

        // in order to load all tags in render method, we have to unset
        // the only one item from $this->tags to make it completely empty.
        // this will make render method to load all existing tags
        unset($this->tags[$tagId]);
        $this->redrawControl();

    }

    public function onTagRemovalFailure()
    {
        $this->flashMessage('Při odstraňování tagu nastala chyba.', 'error');
        $this->redrawControl();
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/tagOverview.latte');

        if (empty($this->tags)) {
            $this->tags = $this->tagFacade->findAllTags(false);
        }

        $template->tags = $this->tags;

        $template->render();
    }
}


interface ITagsOverviewControlFactory
{
    /**
     * @return TagsOverviewControl
     */
    public function create();
}