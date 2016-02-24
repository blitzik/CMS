<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 24.02.2016
 */

namespace Comments;

use App\Components\BaseControl;
use Nette\Application\UI\Form;

class CommentFormControl extends BaseControl
{

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/commentForm.latte');


        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form();

        $form->addText('author', 'Autor', null, Comment::LENGTH_AUTHOR);

        $form->addTextArea('text', 'Text')
                ->setMaxLength(Comment::LENGTH_TEXT);

        $form->addSubmit('send', 'Odeslat komentář');

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }


    public function processForm(Form $form, $values)
    {

    }
}


interface ICommentFormControlFactory
{
    /**
     * @return CommentFormControl
     */
    public function create();
}