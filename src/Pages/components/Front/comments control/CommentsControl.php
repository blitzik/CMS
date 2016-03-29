<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.02.2016
 */

namespace Comments\Components;

use Comments\Components\Front\ICommentsOverviewControlFactory;
use Pages\Exceptions\Runtime\ActionFailedException;
use blitzik\FlashMessages\FlashMessage;
use Comments\Facades\CommentFacade;
use Kdyby\Translation\Translator;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Comments\Comment;
use Pages\Page;

class CommentsControl extends BaseControl
{
    /** @var ICommentsOverviewControlFactory */
    private $overviewControlFactory;

    /** @var ICommentsControlFactory */
    private $commentsControlFactory;

    /** @var CommentFacade */
    private $commentFacade;

    /** @var Translator */
    private $translator;

    /** @var Page */
    private $page;

    /** @var int */
    private $commentsCount = 0;


    public function __construct(
        Page $page,
        Translator $translator,
        CommentFacade $commentFacade,
        ICommentsControlFactory $commentsControlFactory,
        ICommentsOverviewControlFactory $overviewControlFactory
    ) {
        $this->page = $page;
        $this->translator = $translator;
        $this->commentFacade = $commentFacade;
        $this->commentsControlFactory = $commentsControlFactory;
        $this->overviewControlFactory = $overviewControlFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/comments.latte');

        $template->page = $this->page;
        $template->commentsCount = $this->commentsCount;

        $template->render();
    }


    /**
     * @param int $commentsCount
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;
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
        $form->setTranslator($this->translator->domain('page.comments.form.inputs'));

        $form->addText('author', 'author.label', null, Comment::LENGTH_AUTHOR)
                ->setRequired('author.messages.required');

        $form->addTextArea('text', 'text.label', null, 6)
                ->setMaxLength(Comment::LENGTH_TEXT)
                ->setRequired('text.messages.required')
                ->setHtmlId('comment-textarea');

        $form->addSubmit('send', 'submit.caption');

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }


    public function processForm(Form $form, $values)
    {
        if ($this->page->getAllowedComments() === false and !$this->authorizator->isAllowed($this->user, 'page_comment_form', 'comment_on_closed')) {
            $this->flashMessage('page.comments.form.messages.closedComments', FlashMessage::WARNING);
            $this->redirect('this#comments');
        }

        $values['page'] = $this->page;
        try {
            $comment = $this->commentFacade->save((array)$values);

            $this->flashMessage('page.comments.form.messages.success', FlashMessage::SUCCESS);
            $this->redirect('this#comment-' . $comment->getId());

        } catch (ActionFailedException $e) {
            $form->addError($this->translator->translate('page.comments.form.messages.error'));
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