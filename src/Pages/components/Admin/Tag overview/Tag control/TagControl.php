<?php

namespace Tags\Components\Admin;

use App\Components\BaseControl;
use blitzik\FlashMessages\FlashMessage;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Pages\Factories\TagFormFactory;
use Tags\Facades\TagFacade;
use Tags\Tag;

class TagControl extends BaseControl
{
    /** @var array */
    public $onColorChange = [];

    /** @var TagFormFactory */
    private $tagFormFactory;

    /** @var ITranslator */
    private $translator;

    /** @var TagFacade  */
    private $tagFacade;

    /** @var Tag  */
    private $tag;


    public function __construct(
        Tag $tag,
        TagFacade $tagFacade,
        TagFormFactory $tagFormFactory
    ) {
        $this->tag = $tag;
        $this->tagFacade = $tagFacade;
        $this->tagFormFactory = $tagFormFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/tag.latte');

        $template->tag = $this->tag;

        $template->render();
    }


    protected function createComponentTagForm()
    {
        $form = $this->tagFormFactory->create();
        unset($form['name']);

        $form->getElementPrototype()->id = 'form-tag-'.$this->tag->getId();

        $form['color']->setHtmlId('tag-color-input-'.$this->tag->getId())
                      ->setDefaultValue($this->tag->color);

        $form['save']->setHtmlId('tag-submit-'.$this->tag->getId());

        $form->onSuccess[] = [$this, 'processTag'];

        return $form;
    }


    public function processTag(Form $form, $values)
    {
        $this->tag->setColor($values->color);
        try {
            $this->tagFacade->saveTag($this->tag);

            if ($this->presenter->isAjax()) {
                $this->redrawControl();
            } else {
                $this->redirect('this#tag-' . $this->tag->getId());
            }

        } catch (DBALException $e) {
            $this->flashMessage('tags.tagForm.messages.savingError', FlashMessage::ERROR, ['name' => $this->tag['name']]);
            if ($this->presenter->isAjax()) {
                $this->redrawControl('flashes');
            } else {
                $this->redirect('this');
            }
        }
    }


    public function handleRemoveTag($id)
    {
        try {
            $this->tagFacade->removeTag($id);

            if ($this->presenter->isAjax()) {
                $this->redrawControl('tag');
            } else {
                $this->redirect('this');
            }
        } catch (DBALException $e) {
            $this->flashMessage('tags.overview.actions.remove.messages.removeError', FlashMessage::ERROR);
            if ($this->presenter->isAjax()) {
                // value does not matter, in JS we just check existence of this variable
                $this->presenter->payload->errorEl = true;
                $this->redrawControl('flashes');
            } else {
                $this->redirect('this');
            }
        }
    }
}


interface ITagControlFactory
{
    /**
     * @param Tag $tag
     * @return TagControl
     */
    public function create(Tag $tag);
}