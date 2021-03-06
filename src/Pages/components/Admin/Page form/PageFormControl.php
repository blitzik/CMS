<?php

namespace Pages\Components\Admin;

use Pages\Exceptions\Runtime\PagePublicationTimeMissingException;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Pages\Exceptions\Runtime\PageIntroHtmlLengthException;
use Pages\Exceptions\Runtime\PagePublicationTimeException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use blitzik\FlashMessages\FlashMessage;
use Localization\Facades\LocaleFacade;
use Nette\Forms\Controls\SubmitButton;
use Pages\Factories\TagFormFactory;
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

    /** @var TagFormFactory */
    private $tagFormFactory;

    /** @var LocaleFacade */
    private $localeFacade;

    /** @var PageFacade */
    private $pageFacade;

    /** @var Translator */
    private $translator;

    /** @var User */
    private $userEntity;

    /** @var  Page */
    private $page;

    /** @var array */
    private $availableLocales;

    /** @var string */
    private $defaultLocale;


    public function __construct(
        User $user,
        PageFacade $pageFacade,
        ITranslator $translator,
        LocaleFacade $localeFacade,
        TagFormFactory $tagFormFactory,
        IPageTagsPickingControlFactory $articleTagsPickingControlFactory
    ) {
        $this->userEntity = $user;
        $this->pageFacade = $pageFacade;
        $this->translator = $translator;
        $this->localeFacade = $localeFacade;
        $this->tagFormFactory = $tagFormFactory;
        $this->pageTagsPickingControlFactory = $articleTagsPickingControlFactory;

        $this->prepareLocales($this->localeFacade->findAllLocales());
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

        $form->addTextArea('text', 'text.label', null, 25);
                //->setRequired('text.messages.required');

        $form->addText('url', 'url.label', null, 255);

        $form->addSelect('lang', 'lang.label')
                ->setItems($this->availableLocales)
                ->setDefaultValue($this->defaultLocale);

        if (isset($this->page)) {
            $form['lang']->setDisabled();
            //$form['lang']->setOmitted();
        }

        $form->addCheckbox('allowedComments', 'allowedComments.label')
                ->setDefaultValue(true);

        $form->addText('keywords', 'keywords.label');
        $form->addText('description', 'description.label');

        $form->addSubmit('saveAndPublish', 'saveAndPublish.caption')
                ->setAttribute('title', $this->translator->translate('pageEditForm.saveAndPublish.title'))
                ->onClick[] = [$this, 'processPageSavingAndPublishing'];

        $form->addSubmit('saveAsDraft', 'saveAsDraft.caption')
                ->setAttribute('title', $this->translator->translate('pageEditForm.saveAsDraft.title'))
                ->onClick[] = [$this, 'processPageSavingAsDraft'];

        if (!$this->authorizator->isAllowed($this->user, 'page', 'create') or
            !$this->authorizator->isAllowed($this->user, 'page', 'edit')) {
            $form['saveAndPublish']->setDisabled();
            $form['saveAsDraft']->setDisabled();
        }

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
        if (!$this->authorizator->isAllowed($this->user, 'page', 'create') or
            !$this->authorizator->isAllowed($this->user, 'page', 'edit')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            return;
        }
        
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

        } catch (PageIntroHtmlLengthException $pi) {
            $form->addError($this->translator->translate('pageEditForm.messages.pageIntroHtmlLength'));
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
        $this['pageForm']['publishedAt']->setDefaultValue($page->getTitle());

        if ($page->getPublishedAt() !== null) {
            $this['pageForm']['publishedAt']->setDefaultValue($page->getPublishedAt()->format('j.n.Y H:i'));
        }

        $this['pageForm']['title']->setDefaultValue($page->getTitle());
        $this['pageForm']['intro']->setDefaultValue($page->getIntro());
        $this['pageForm']['text']->setDefaultValue($page->getText());
        $this['pageForm']['allowedComments']->setDefaultValue($page->getAllowedComments());

        $this['pageForm']['description']->setDefaultValue($page->getMetaDescription());
        $this['pageForm']['keywords']->setDefaultValue($page->getMetaKeywords());
        $this['pageForm']['lang']->setDefaultValue($page->getLocaleName());
    }


    private function prepareLocales(array $locales)
    {
        foreach ($locales as $name => $locale) {
            $this->availableLocales[$locale['name']] = $locale['code'];

            if ($locale['default'] === true) {
                $this->defaultLocale = $locale['name'];
            }
        }
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