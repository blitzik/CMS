<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.02.2016
 */

namespace Comments\Components;

use blitzik\FlashMessages\FlashMessage;
use Comments\Components\Front\ICommentsOverviewControlFactory;
use Comments\Facades\CommentFacade;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Comments\Comment;
use Pages\Exceptions\Runtime\ActionFailedException;
use Pages\Page;

class CommentsControl extends BaseControl
{
    /** @var ICommentsOverviewControlFactory */
    private $overviewControlFactory;

    /** @var ICommentsControlFactory */
    private $commentsControlFactory;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var Page */
    private $page;


    public function __construct(
        Page $page,
        CommentFacade $commentFacade,
        ICommentsControlFactory $commentsControlFactory,
        ICommentsOverviewControlFactory $overviewControlFactory
    ) {
        $this->page = $page;
        $this->commentFacade = $commentFacade;
        $this->commentsControlFactory = $commentsControlFactory;
        $this->overviewControlFactory = $overviewControlFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/comments.latte');

        $template->page = $this->page;

        $template->render();
    }


    /**
     * @Actions show
     */
    protected function createComponentCommentsOverview()
    {
        $comp = $this->overviewControlFactory->create($this->page);
        return $comp;
    }


    protected function createComponentForm()
    {
        $form = new Form();

        $form->addText('author', 'Autor', null, Comment::LENGTH_AUTHOR)
                ->setRequired('Podepište se pod komentář prosím :-) (pole autor)');

        $form->addTextArea('text', 'Text', null, 6)
                ->setMaxLength(Comment::LENGTH_TEXT)
                ->setRequired('Vyplňte prosím text komentáře')
                ->setHtmlId('comment-textarea');

        $form->addSubmit('send', 'Odeslat komentář');

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }


    public function processForm(Form $form, $values)
    {
        $values['page'] = $this->page;
        try {
            $comment = $this->commentFacade->save((array)$values);

            $this->flashMessage('Komentář  byl úspěšně uložen', 'success');
            $this->redirect('this#comment-' . $comment->getId());

        } catch (ActionFailedException $e) {
            $this->flashMessage('Při ukládání komentáře došlo k chybě', FlashMessage::ERROR);
            $this->redirect('this#new-comment-form');
        }
    }
}


interface ICommentsControlFactory
{
    /**
     * @param Page $page
     * @return CommentsControl
     */
    public function create(Page $page);
}