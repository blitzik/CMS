<?php

namespace Pages\Components\Admin;

use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Strings;
use Pages\Exceptions\Runtime\PageTitleAlreadyExistsException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Pages\Facades\PageFacade;
use App\Components\BaseControl;
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


    public function __construct(
        User $user,
        PageFacade $pageFacade,
        IPageTagsPickingControlFactory $articleTagsPickingControlFactory
    )
    {
        $this->pageFacade = $pageFacade;
        $this->user = $user;
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
        $form->getElementPrototype()->id = 'page-form';

        $form->addText('title', 'Titulek (*)', null, Page::LENGTH_TITLE)
            ->setMaxLength(Page::LENGTH_TITLE)
            ->setHtmlId('form-page-title')
            ->setRequired('Vyplňte titulek článku')
            ->setAttribute('data-text-length', Page::LENGTH_TITLE)
            ->addRule(Form::MAX_LENGTH, 'Titulek článku může obsahovat pouze %d znaků', Page::LENGTH_TITLE);

        $form->addText('publishedAt', 'Datum publikace (*)', null, 16)
            ->setHtmlId('datetimepicker')
            //->setRequired('Nastavte datum publikování článku')
            ->addRule(Form::MAX_LENGTH, 'Neplatná délka řetězce (publikování článku)', 16);

        $form->addTextArea('intro', 'Úvodní text článku (*)', null, 7)
            ->setMaxLength(Page::LENGTH_INTRO)
            ->setHtmlId('form-page-intro')
            ->setRequired('Vyplňte text úvodu článku')
            ->setAttribute('data-text-length', Page::LENGTH_INTRO)
            ->addRule(Form::MAX_LENGTH, 'Úvod článku může obsahovat pouze %d znaků', Page::LENGTH_INTRO);

        $form->addTextArea('text', 'Text článku (*)', null, 25)
            ->setRequired('Vyplňte text článku')
            ->setHtmlId('page-form-text');

        $form->addText('url', 'Url adresa', null, 255);

        $form->addSubmit('saveAndPublish', 'Uložit a publikovat')
                ->onClick[] = [$this, 'processPageSavingAndPublishing'];

        $form->addSubmit('saveAndHide', 'Uložit a skrýt')
                ->onClick[] = [$this, 'processPageSavingAndHiding'];

        $form->addProtection();


        $form->onValidate[] = [$this, 'checkPublishing'];

        return $form;
    }


    public function checkPublishing(Form $form)
    {
        if ($form['saveAndHide']->isSubmittedBy()) {
            return;
        }

        // if the form was submitted by save and publish button
        if (empty($form['publishedAt']->value)) {
            $form->addError('Aby mohl být článek publikován, musíte nastavit datum publikace.');
        }

        $this->redrawControl();
    }


    public function processPageSavingAndPublishing(SubmitButton $buttonControl)
    {
        $this->pageSaving($buttonControl->getForm(), true);
    }


    public function processPageSavingAndHiding(SubmitButton $buttonControl)
    {
        $this->pageSaving($buttonControl->getForm(), false);
    }


    private function pageSaving(\Nette\Forms\Form $form, $isPublished)
    {
        $values = $form->getValues(true);
        $values['isPublished'] = (bool)$isPublished;
        $values['author'] = $this->user;

        $tags = $form->getHttpData(Form::DATA_TEXT, 'tags[]');
        $values['tags'] = $tags;

        try {
            $page = $this->pageFacade->save($values, $this->page);
            $this->flashMessage(
                'Článek byl úspěšně uložen a ' . ($values['isPublished'] ? 'publikován' : 'skryt'),
                'success'
            );

        } catch (PageTitleAlreadyExistsException $at) {
            $form->addError('Článek s tímto titulkem již existuje. Zvolte jiný titulek.');

            return;
        } catch (UrlAlreadyExistsException $ur) {
            $form['url']->setValue(Strings::webalize($values['title'], '/'));
            $form->addError('URL adresa článku již existuje. Změňte titulek článku nebo URL adresu.');

            return;
        } catch (DBALException $e) {
            $form->addError('Při ukládání došlo k chybě');

            return;
        }

        $this->onSuccessPageSaving($this, $page);
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