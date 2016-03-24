<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 14.03.2016
 */

namespace Pages\Components;

use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use blitzik\FlashMessages\FlashMessage;
use Pages\Factories\TagFormFactory;
use Kdyby\Translation\Translator;
use Doctrine\DBAL\DBALException;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Tags\Facades\TagFacade;
use Users\Authorization\Permission;

class TagFormControl extends BaseControl
{
    public $onSuccessTagSaving;

    /** @var TagFormFactory */
    private $tagFormFactory;

    /** @var Translator */
    private $translator;

    /** @var TagFacade */
    private $tagFacade;

    /** @var bool */
    private $isAjaxified = false;


    public function __construct(
        TagFacade $tagFacade,
        Translator $translator,
        TagFormFactory $tagFormFactory
    ) {
        $this->tagFacade = $tagFacade;
        $this->translator = $translator;
        $this->tagFormFactory = $tagFormFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/tagForm.latte');

        $template->render();
    }


    public function setAsAjaxified()
    {
        $this->isAjaxified = true;
    }


    protected function createComponentTagForm()
    {
        $form = $this->tagFormFactory->create();
        $form->getElementPrototype()->id = '#new-tag-form';

        $form['color']->setHtmlId('creation-form-color');

        if ($this->isAjaxified) {
            $form->getElementPrototype()->class = 'ajax';
        }

        $form->onSuccess[] = [$this, 'processNewTag'];

        if (!$this->user->isAllowed('page_tag', Permission::ACL_CREATE)) {
            $form['save']->setDisabled();
        }

        $form->addProtection();
        
        return $form;
    }


    public function processNewTag(Form $form, $values)
    {
        if (!$this->user->isAllowed('page_tag', Permission::ACL_CREATE)) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            return;
        }

        try {
            $tag = $this->tagFacade->saveTag((array)$values);

            $this->onSuccessTagSaving($tag, $this);

        } catch (TagNameAlreadyExistsException $t) {
            $form->addError($this->translator->translate('tags.tagForm.messages.nameExists', ['name' => $values['name']]));

        } catch (UrlAlreadyExistsException $url) {
            $form->addError($this->translator->translate('tags.tagForm.messages.tagUrlExists'));

        } catch (DBALException $e) {
            $form->addError($this->translator->translate('tags.tagForm.messages.savingError'));
        }
    }
}


interface ITagFormControlFactory
{
    /**
     * @return TagFormControl
     */
    public function create();
}