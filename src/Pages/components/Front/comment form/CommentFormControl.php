<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 24.02.2016
 */

namespace Comments\Components\Front;

use Comments\Facades\CommentFacade;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Comments\Comment;
use Pages\Page;

class CommentFormControl extends BaseControl
{
    /** @var CommentFacade */
    private $commentFacade;

    /** @var Page */
    private $page;


    public function __construct(
        Page $page,
        CommentFacade $commentFacade
    ) {
        $this->page = $page;
        $this->commentFacade = $commentFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/commentForm.latte');


        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form();

        $form->addText('author', 'Autor', null, Comment::LENGTH_AUTHOR)
                ->setRequired('Podepište se pod komentář prosím :-) (pole autor)');

        $form->addTextArea('text', 'Text', null, 6)
                ->setMaxLength(Comment::LENGTH_TEXT)
                ->setRequired('Vyplňte prosím text komentáře');

        $form->addSubmit('send', 'Odeslat komentář');

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }


    public function processForm(Form $form, $values)
    {
        $comment = new Comment($values->author, $values->text, $this->page);

        $this->commentFacade->saveComment($comment);

        $this->flashMessage('Komentář  byl úspěšně uložen', 'success');
        $this->redirect('this');
    }
}


interface ICommentFormControlFactory
{
    /**
     * @param Page $page
     * @return CommentFormControl
     */
    public function create(Page $page);
}