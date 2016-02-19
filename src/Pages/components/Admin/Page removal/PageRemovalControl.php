<?php

namespace Pages\Components\Admin;

use App\Components\BaseControl;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Pages\Page;
use Pages\Facades\PageFacade;

class PageRemovalControl extends BaseControl
{
    /** @var array */
    public $onPageRemoval = [];
    public $onCancelClick = [];

    /** @var PageFacade */
    private $pageFacade;

    /** @var  Page */
    private $page;


    public function __construct(
        Page $page,
        PageFacade $pageFacade
    ) {
        $this->page = $page;
        $this->pageFacade = $pageFacade;
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

        $form->addText('check', 'Do textového pole opište titulek článku pro jeho smazání:')
            ->setRequired('Vyplňte kontrolní text, aby mohl být článek smazán.')
            ->addRule(Form::EQUAL, 'Nesouhlasí kontrolní text.', $this->page->title);

        $form->addSubmit('remove', 'Nenávratně článek smazat')
            ->onClick[] = [$this, 'removePage'];

        $form->addSubmit('cancel', 'Vrátit se zpět')
            ->setValidationScope([])
            ->onClick[] = [$this, 'cancelClick'];

        return $form;
    }


    public function removePage(SubmitButton $button)
    {
        try {
            $this->pageFacade->removePage($this->page);
        } catch (DBALException $e) {
            $this->flashMessage('Při mazání článku došlo k chybě', 'error');
            $this->redirect('this');
        }

        $this->onPageRemoval($this);
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