<?php

namespace Tags\Components\Admin;

use blitzik\FlashMessages\FlashMessage;
use Nette\Localization\ITranslator;
use Pages\Factories\TagFormFactory;
use Doctrine\DBAL\DBALException;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
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
                      ->setDefaultValue($this->tag->getColor());

        $form['save']->setHtmlId('tag-submit-'.$this->tag->getId());

        $form->onSuccess[] = [$this, 'processTag'];

        $form->addProtection();

        if (!$this->authorizator->isAllowed($this->user, 'page_tag', 'edit')) {
            $form['save']->setDisabled();
        }

        return $form;
    }


    public function processTag(Form $form, $values)
    {
        if (!$this->authorizator->isAllowed($this->user, 'page_tag', 'edit')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            return;
        }

        try {
            $this->tagFacade->saveTag((array)$values, $this->tag);

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


    public function handleRemoveTag()
    {
        if (!$this->authorizator->isAllowed($this->user, 'page_tag', 'remove')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            if ($this->presenter->isAjax()) {
                $this->presenter->payload->errorEl = 'no permission';
                $this->redrawControl('tag');
                return;
            } else {
                $this->redirect('this');
            }
        }

        try {
            $this->tagFacade->removeTag($this->tag->getId());

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