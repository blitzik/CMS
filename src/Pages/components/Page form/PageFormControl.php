<?php

namespace Pages\Components;

use App\Exceptions\Runtime\PageTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Pages\Facades\PageFacade;
use App\BaseControl;
use Pages\Page;
use Users\User;
use Tags\Tag;

class PageFormControl extends BaseControl
{
    /** @var IPageTagsPickingControlFactory */
    private $pageTagsPickingControlFactory;

    /** @var PageFacade */
    private $pageFacade;

    /** @var User */
    private $user;

    /** @var  Page */
    private $page;

    /** @var  Tag[] */
    private $tags;


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

        $template->form = $this['pageForm'];

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

        $form->addText('time', 'Datum publikování článku', null, 16)
            ->setHtmlId('datetimepicker')
            //->setRequired('Nastavte datum publikování článku')
            ->addRule(Form::MAX_LENGTH, 'Neplatná délka řetězce (publikování článku)', 16);

        $form->addCheckbox('isPublished', 'Publikovat článek')
            ->setHtmlId('checkbox-is-published')
            ->setDefaultValue(false);

        $form->addTextArea('intro', 'Úvodní text článku (*)', null, 7)
            ->setMaxLength(Page::LENGTH_INTRO)
            ->setHtmlId('form-page-intro')
            ->setRequired('Vyplňte text úvodu článku')
            ->setAttribute('data-text-length', Page::LENGTH_INTRO)
            ->addRule(Form::MAX_LENGTH, 'Úvod článku může obsahovat pouze %d znaků', Page::LENGTH_INTRO);

        $form->addTextArea('text', 'Text článku (*)', null, 25)
            ->setRequired('Vyplňte text článku')
            ->setHtmlId('page-form-text');

        $form->addSubmit('save', 'Uložit článek');

        $form->addProtection();


        $form->onValidate[] = [$this, 'checkPublishing'];
        $form->onSuccess[] = [$this, 'processPageSaving'];

        return $form;
    }


    public function checkPublishing(Form $form)
    {
        $values = $form->getValues();
        if (empty($form['time']->value) and $values->isPublished == true) {
            $form->addError('Aby mohl být článek publikován, musíte nastavit datum publikace.');
        }

        $this->redrawControl();
    }


    public function processPageSaving(Form $form, $values)
    {

        $tags = $form->getHttpData(Form::DATA_TEXT, 'tags[]');

        if ($this->page !== null) {
            $page = $this->page;

        } else {
            $page = new Page(
                $values->title,
                $values->intro,
                $values->text,
                $this->user
            );
        }

        /*if (!empty($tags)) {
            $tags = \array_intersect_key($this->tags, $tags);
        }*/

        try {
            $this->pageFacade->save($page, $values, $tags);
            $this->flashMessage('Článek byl úspěšně uložen.', 'success');

        } catch (PageTitleAlreadyExistsException $at) {
            $form->addError('Článek s tímto titulkem již existuje');

            return;
        } catch (UrlAlreadyExistsException $ur) {
            $form->addError('URL s tímto titulkem již existuje.');

            return;
        } catch (DBALException $e) {
            $form->addError('Při ukládání došlo k chybě');

            return;
        }

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
        } else {
            $this->redirect('this');
        }
    }


    public function fillFormBy(Page $page)
    {
        $this['pageForm']['title']->setDefaultValue($page->title);

        if ($page->getPublishedAt() !== null) {
            $this['pageForm']['time']->setDefaultValue($page->publishedAt->format('j.n.Y H:i'));
        }

        $this['pageForm']['isPublished']->setDefaultValue($page->isPublished);
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