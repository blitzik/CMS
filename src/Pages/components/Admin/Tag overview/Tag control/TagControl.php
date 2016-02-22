<?php

namespace Tags\Components;

use App\Components\BaseControl;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Pages\Factories\TagFormFactory;
use Tags\Facades\TagFacade;

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

    /** @var array  */
    private $tag;


    public function __construct(
        array $tag,
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

        $template->tag = ArrayHash::from($this->tag);

        $template->render();
    }


    protected function createComponentTagForm()
    {
        $form = $this->tagFormFactory->create();
        unset($form['name']);

        $form->getElementPrototype()->id = 'form-tag-'.$this->tag['id'];

        $form['color']->setHtmlId('tag-color-input-'.$this->tag['id'])
                      ->setDefaultValue($this->tag['color']);

        $form['save']->setHtmlId('tag-submit-'.$this->tag['id']);

        $form->onSuccess[] = [$this, 'processTag'];

        return $form;
    }


    public function processTag(Form $form, $values)
    {
        try {
            $this->tagFacade->changeColor($this->tag['id'], $values->color);

            if ($this->presenter->isAjax()) {
                $this->tag['color'] = $values->color;
                $this->redrawControl();
            } else {
                $this->redirect('this#tag-' . $this->tag['id']);
            }

        } catch (DBALException $e) {
            $this->flashMessage('tags.tagForm.messages.savingError', 'error', null, ['name' => $this->tag['name']]);
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
            $this->flashMessage('tags.overview.actions.remove.messages.removeError', 'error');
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
     * @param array $tag
     * @return TagControl
     */
    public function create(array $tag);
}