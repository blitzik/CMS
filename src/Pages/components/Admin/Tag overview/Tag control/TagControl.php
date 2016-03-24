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
use Users\Authorization\Permission;

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

        $form->addProtection();

        if (!$this->user->isAllowed('page_tag', Permission::ACL_EDIT)) {
            $form['save']->setDisabled();
        }

        return $form;
    }


    public function processTag(Form $form, $values)
    {
        if (!$this->user->isAllowed('page_tag', Permission::ACL_EDIT)) {
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
        if (!$this->user->isAllowed('page_tag', Permission::ACL_REMOVE)) {
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