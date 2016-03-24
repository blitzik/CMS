<?php

namespace Pages\Components\Admin;

use App\Components\BaseControl;
use blitzik\FlashMessages\FlashMessage;
use Doctrine\DBAL\DBALException;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Localization\ITranslator;
use Pages\Page;
use Pages\Facades\PageFacade;
use Users\Authorization\Permission;

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

        if (!$this->user->isAllowed('page', Permission::ACL_REMOVE)) {
            $form['remove']->setDisabled();
        }

        $form->addProtection();
        
        return $form;
    }


    public function removePage(SubmitButton $button)
    {
        if (!$this->user->isAllowed('page', Permission::ACL_REMOVE)) {
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