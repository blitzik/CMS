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

class TagFormControl extends BaseControl
{
    /** @var TagFormFactory */
    private $tagFormFactory;

    /** @var Translator */
    private $translator;

    /** @var TagFacade */
    private $tagFacade;


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


    protected function createComponentTagForm()
    {
        $form = $this->tagFormFactory->create();

        $form['color']->setHtmlId('creation-form-color');

        $form->onSuccess[] = [$this, 'processNewTag'];

        return $form;
    }


    public function processNewTag(Form $form, $values)
    {
        try {
            $tag = $this->tagFacade->saveTag((array)$values);

            $this->flashMessage('tags.tagForm.messages.success', FlashMessage::SUCCESS, ['name' => $tag->getName()]);
            $this->redirect('this');

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