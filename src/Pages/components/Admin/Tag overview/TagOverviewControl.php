<?php

namespace Tags\Components\Admin;

use Kdyby\Translation\Translator;
use Nette\Forms\Controls\SubmitButton;
use Nette\Application\UI\Multiplier;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Tags\Facades\TagFacade;
use Tags\Query\TagQuery;
use Tags\Tag;

class TagsOverviewControl extends BaseControl
{
    /** @var array  */
    public $onMissingTag = [];

    /** @persistent */
    public $name;

    /** @var ITagControlFactory  */
    private $tagControlFactory;

    /** @var Translator */
    private $translator;

    /** @var TagFacade  */
    private $tagFacade;

    /** @var  array */
    private $tags = [];


    public function __construct(
        TagFacade $tagFacade,
        Translator $translator,
        ITagControlFactory $tagControlFactory
    ) {
        $this->tagFacade = $tagFacade;
        $this->translator = $translator;
        $this->tagControlFactory = $tagControlFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/tagOverview.latte');

        $tagQuery = new TagQuery();
        $tagQuery->orderByID('DESC')
                 ->indexedByTagId();

        if ($this->name !== null) {
            $tagQuery->likeTagName($this->name);
        }

        if (empty($this->tags)) {
            $this->tags = $this->tagFacade
                               ->fetchTags($tagQuery)
                               ->toArray();
        }

        $template->tags = $this->tags;

        $template->render();
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
     * @param int $tagId
     * @return Tag
     */
    private function getTag($tagId)
    {
        if (empty($this->tags)) {
            // if processing "handle" method, $this->tags is always empty array
            // because this factory is invoked before render method
            $tag = $this->tagFacade->getById($tagId);
            if ($tag === null) { // trying to request non-existing tag
                $this->onMissingTag($this); // there is happening redirect
            }
            $this->tags[$tagId] = $tag;
        } else { // common request
            $tag = $this->tags[$tagId];
        }

        return $tag;
    }


    protected function createComponentFilter()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('tags.filter.form'));
        $form->getElementPrototype()->class = 'ajax';

        $form->addText('name', 'name.label')
                ->setDefaultValue($this->name);

        $form->addSubmit('filter', 'filter.label')
                ->onClick[] = [$this, 'filterTags'];


        $form->addSubmit('reset', 'reset.label')
                ->onClick[] = [$this, 'filterReset'];

        return $form;
    }


    public function filterTags(SubmitButton $button)
    {
        $values = $button->getForm()->getValues();
        $this->name = $values['name'];

        if (!$this->presenter->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('tagsList');
        }
    }


    public function filterReset(SubmitButton $button)
    {
        $this->name = null;
        if (!$this->presenter->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('tagsList');
        }
    }

}


interface ITagsOverviewControlFactory
{
    /**
     * @return TagsOverviewControl
     */
    public function create();
}