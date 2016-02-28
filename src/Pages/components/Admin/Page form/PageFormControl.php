<?php

namespace Pages\Components\Admin;

use blitzik\FlashMessages\FlashMessage;
use Pages\Exceptions\Runtime\PagePublicationTimeException;
use Pages\Exceptions\Runtime\PagePublicationTimeMissingException;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Localization\ITranslator;
use Kdyby\Translation\Translator;
use Doctrine\DBAL\DBALException;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Kdyby\Translation\Phrase;
use Pages\Facades\PageFacade;
use Nette\Utils\Strings;
use Pages\Page;
use Users\User;

class PageFormControl extends BaseControl
{
    /** @var array */
    public $onSuccessPageSaving = [];

    /** @var IPageTagsPickingControlFactory */
    private $pageTagsPickingControlFactory;

    /** @var PageFacade */
    private $pageFacade;

    /** @var User */
    private $user;

    /** @var  Page */
    private $page;

    /** @var Translator */
    private $translator;


    public function __construct(
        User $user,
        PageFacade $pageFacade,
        ITranslator $translator,
        IPageTagsPickingControlFactory $articleTagsPickingControlFactory
    ) {
        $this->user = $user;
        $this->pageFacade = $pageFacade;
        $this->translator = $translator;
        $this->pageTagsPickingControlFactory = $articleTagsPickingControlFactory;
    }


    protected function createComponentPageTagsPicking()
    {
        $comp = $this->pageTagsPickingControlFactory->create($this->page);

        return $comp;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/pageForm.latte');

        if (isset($this->page)) {
            $this->fillFormBy($this->page);
        }

        $template->page = $this->page;
        //$template->form = $this['pageForm'];

        $template->render();
    }


    public function setPageToEdit(Page $page)
    {
        $this->page = $page;
    }


    protected function createComponentPageForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator->domain('pageEditForm'));

        $form->getElementPrototype()->id = 'page-form';

        $form->addText('title', 'title.label', null, Page::LENGTH_TITLE)
                ->setMaxLength(Page::LENGTH_TITLE)
                ->setRequired('title.messages.required')
                ->setAttribute('data-text-length', Page::LENGTH_TITLE)
                ->addRule(Form::MAX_LENGTH, new Phrase('title.messages.maxLength', ['numChars' => Page::LENGTH_TITLE]), Page::LENGTH_TITLE);

        $form->addText('publishedAt', 'publishedAt.label', null, 16)
                ->setHtmlId('datetimepicker')
                ->setRequired('publishedAt.messages.required')
                ->addCondition(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, new Phrase('publishedAt.messages.maxLength', ['numChars' => 16]), 16);

        $form->addTextArea('intro', 'intro.label', null, 7)
                ->setMaxLength(Page::LENGTH_INTRO)
                ->setRequired('intro.messages.required')
                ->setAttribute('data-text-length', Page::LENGTH_INTRO)
                ->addRule(Form::MAX_LENGTH, new Phrase('intro.messages.maxLength', ['numChars' => Page::LENGTH_INTRO]), Page::LENGTH_INTRO);

        $form->addTextArea('text', 'text.label', null, 25)
                ->setRequired('text.messages.required');

        $form->addText('url', 'url.label', null, 255);

        $form->addCheckbox('allowedComments', 'allowedComments.label')
                ->setDefaultValue(true);

        $form->addSubmit('saveAndPublish', 'saveAndPublish.caption')
                ->setAttribute('title', $this->translator->translate('pageEditForm.saveAndPublish.title'))
                ->onClick[] = [$this, 'processPageSavingAndPublishing'];

        $form->addSubmit('saveAsDraft', 'saveAsDraft.caption')
                ->setAttribute('title', $this->translator->translate('pageEditForm.saveAsDraft.title'))
                ->onClick[] = [$this, 'processPageSavingAsDraft'];

        $form->addProtection();

        return $form;
    }


    public function processPageSavingAndPublishing(SubmitButton $buttonControl)
    {
        $this->pageSaving($buttonControl->getForm(), false);
    }


    public function processPageSavingAsDraft(SubmitButton $buttonControl)
    {
        $this->pageSaving($buttonControl->getForm(), true);
    }


    private function pageSaving(\Nette\Forms\Form $form, $isDraft)
    {
        $values = $form->getValues(true);
        $values['saveAsDraft'] = (bool)$isDraft;
        $values['author'] = $this->user;

        $tags = $form->getHttpData(Form::DATA_TEXT, 'tags[]');
        $values['tags'] = $tags;

        try {
            $page = $this->pageFacade->save($values, $this->page);
            $this->flashMessage(
                'pageEditForm.messages.success' . ($values['saveAsDraft'] ? 'Draft' : 'Publish'),
                FlashMessage::SUCCESS
            );

            $this->onSuccessPageSaving($this, $page);

        } catch (PagePublicationTimeMissingException $ptm) {
            $form->addError($this->translator->translate('pageEditForm.messages.missingPublicationTime'));
            return;

        } catch (PagePublicationTimeException $pt) {
            $form->addError($this->translator->translate('pageEditForm.messages.publishedPageInvalidPublicationTime'));
            return;

        } catch (PageTitleAlreadyExistsException $at) {
            $form->addError($this->translator->translate('pageEditForm.messages.titleExists'));
            return;

        } catch (UrlAlreadyExistsException $ur) {
            $form['url']->setValue(Strings::webalize($values['title'], '/'));
            $form->addError($this->translator->translate('pageEditForm.messages.urlExists'));
            return;

        } catch (DBALException $e) {
            $form->addError($this->translator->translate('pageEditForm.messages.savingError'));
            return;
        }
    }


    private function fillFormBy(Page $page)
    {
        $this['pageForm']['url']->setDefaultValue($page->getUrlPath());
        $this['pageForm']['publishedAt']->setDefaultValue($page->title);

        if ($page->getPublishedAt() !== null) {
            $this['pageForm']['publishedAt']->setDefaultValue($page->publishedAt->format('j.n.Y H:i'));
        }

        $this['pageForm']['title']->setDefaultValue($page->title);
        $this['pageForm']['intro']->setDefaultValue($page->intro);
        $this['pageForm']['text']->setDefaultValue($page->text);
        $this['pageForm']['allowedComments']->setDefaultValue($page->getAllowedComments());
    }
}


interface IPageFormControlFactory
{
    /**
     * @param User $user
     * @return PageFormControl
     */
    public function create(User $user);
}