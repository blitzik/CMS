<?php

namespace Pages\Components;

use App\BaseControl;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Pages\Article;
use Pages\Facades\PageFacade;

class ArticleRemovalControl extends BaseControl
{
    /** @var array  */
    public $onArticleRemoval = [];
    public $onCancelClick = [];

    /** @var PageFacade  */
    private $pageFacade;

    /** @var  Article */
    private $article;

    public function __construct(
        Article $article,
        PageFacade $pageFacade
    ) {
        $this->article = $article;
        $this->pageFacade = $pageFacade;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/articleRemoval.latte');

        $template->article = $this->article;

        $template->render();
    }

    protected function createComponentRemovalForm()
    {
        $form = new Form;

        $form->addText('check', 'Do textového pole opište titulek článku pro jeho smazání:')
            ->setRequired('Vyplňte kontrolní text, aby mohl být článek smazán.')
            ->addRule(Form::EQUAL, 'Nesouhlasí kontrolní text.', $this->article->title);

        $form->addSubmit('remove', 'Nenávratně článek smazat')
            ->onClick[] = [$this, 'removeArticle'];

        $form->addSubmit('cancel', 'Vrátit se zpět')
            ->setValidationScope([])
            ->onClick[] = [$this, 'cancelClick'];

        return $form;
    }

    public function removeArticle(SubmitButton $button)
    {
        $this->pageFacade->removeArticle($this->article->getId());

        $this->onArticleRemoval($this);
    }

    public function cancelClick(SubmitButton $button)
    {
        $this->onCancelClick($this);
    }
}


interface IArticleRemovalControlFactory
{
    /**
     * @param Article $article
     * @return ArticleRemovalControl
     */
    public function create(Article $article);
}