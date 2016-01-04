<?php

namespace Tags\Components;

use App\Components\BaseControl;
use Nette\Application\UI\Multiplier;
use Tags\Facades\TagFacade;

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
            $tag = $this->getTag($tagId);
            $comp = $this->tagControlFactory->create($tag);

            return $comp;
        });
    }


    /**
     * @param $tagId
     * @return array
     */
    private function getTag($tagId)
    {
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

        return $tag;
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