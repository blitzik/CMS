<?php

namespace Pages\Components\Admin;

use blitzik\FlashMessages\FlashMessage;
use Nette\Forms\Controls\SubmitButton;
use Users\Authorization\Permission;
use Nette\Localization\ITranslator;
use Kdyby\Translation\Translator;
use Doctrine\DBAL\DBALException;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Pages\Facades\PageFacade;
use Pages\Page;

class PageRemovalControl extends BaseControl
{
    /** @var array */
    public $onPageRemoval = [];
    public $onCancelClick = [];

    /** @var PageFacade */
    private $pageFacade;

    /** @var Translator */
    private $translator;

    /** @var Page */
    private $page;


    public function __construct(
        Page $page,
        PageFacade $pageFacade,
        ITranslator $translator
    ) {
        $this->page = $page;
        $this->pageFacade = $pageFacade;
        $this->translator = $translator;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/pageRemoval.latte');

        $template->page = $this->page;

        $template->render();
    }


    protected function createComponentRemovalForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('pageRemoval'));

        $form->addText('check', 'check.label')
                ->setRequired('check.messages.required')
                ->addRule(Form::EQUAL, 'check.messages.notEqual', $this->page->title);

        $form->addSubmit('remove', 'remove.caption')
                ->onClick[] = [$this, 'removePage'];

        $form->addSubmit('cancel', 'cancel.caption')
                ->setValidationScope([])
                ->onClick[] = [$this, 'cancelClick'];

        if (!$this->authorizator->isAllowed($this->user, 'page', 'remove')) {
            $form['remove']->setDisabled();
        }

        $form->addProtection();
        
        return $form;
    }


    public function removePage(SubmitButton $button)
    {
        if (!$this->authorizator->isAllowed($this->user, 'page', 'remove')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            return;
        }

        try {
            $this->pageFacade->removePage($this->page);
        } catch (DBALException $e) {
            $this->flashMessage('pageRemoval.flashMessages.savingError', FlashMessage::ERROR);
            $this->redirect('this');
        }

        $this->onPageRemoval($this, $this->page);
    }


    public function cancelClick(SubmitButton $button)
    {
        $this->onCancelClick($this);
    }
}


interface IPageRemovalControlFactory
{
    /**
     * @param Page $page
     * @return PageRemovalControl
     */
    public function create(Page $page);
}