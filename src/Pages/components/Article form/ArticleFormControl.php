<?php

namespace Pages\Components;

use App\Exceptions\Runtime\ArticleTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Pages\Facades\PageFacade;
use App\BaseControl;
use Pages\Article;
use Users\User;
use Tags\Tag;

class ArticleFormControl extends BaseControl
{
    /** @var IArticleTagsPickingControlFactory  */
    private $articleTagsPickingControlFactory;

    /** @var PageFacade  */
    private $pageFacade;

    /** @var User  */
    private $user;

    /** @var  Article */
    private $article;

    /** @var  Tag[] */
    private $tags;

    public function __construct(
        User $user,
        PageFacade $pageFacade,
        IArticleTagsPickingControlFactory $articleTagsPickingControlFactory
    ) {
        $this->pageFacade = $pageFacade;
        $this->user = $user;
        $this->articleTagsPickingControlFactory = $articleTagsPickingControlFactory;
    }

    protected function createComponentArticleTagsPicking()
    {
        $comp = $this->articleTagsPickingControlFactory->create($this->article);

        return $comp;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/articleForm.latte');

        if (isset($this->article)) {
            $this->fillFormBy($this->article);
        }

        $template->form = $this['articleForm'];

        $template->render();
    }

    public function setArticleToEdit(Article $article)
    {
        $this->article = $article;
    }

    protected function createComponentArticleForm()
    {
        $form = new Form();
        $form->getElementPrototype()->id = 'article-form';

        $form->addText('title', 'Titulek (*)', null, Article::LENGTH_TITLE)
                ->setMaxLength(Article::LENGTH_TITLE)
                ->setHtmlId('form-article-title')
                ->setRequired('Vyplňte titulek článku')
                ->setAttribute('data-text-length', Article::LENGTH_TITLE)
                ->addRule(Form::MAX_LENGTH, 'Titulek článku může obsahovat pouze %d znaků', Article::LENGTH_TITLE);

        $form->addText('time', 'Datum publikování článku', null, 16)
                ->setHtmlId('datetimepicker')
                //->setRequired('Nastavte datum publikování článku')
                ->addRule(Form::MAX_LENGTH, 'Neplatná délka řetězce (publikování článku)', 16);

        $form->addCheckbox('isPublished', 'Publikovat článek')
                ->setHtmlId('checkbox-is-published')
                ->setDefaultValue(false);

        $form->addTextArea('intro', 'Úvodní text článku (*)', null, 7)
                ->setMaxLength(Article::LENGTH_INTRO)
                ->setHtmlId('form-article-intro')
                ->setRequired('Vyplňte text úvodu článku')
                ->setAttribute('data-text-length', Article::LENGTH_INTRO)
                ->addRule(Form::MAX_LENGTH, 'Úvod článku může obsahovat pouze %d znaků', Article::LENGTH_INTRO);

        $form->addTextArea('text', 'Text článku (*)', null, 25)
                ->setRequired('Vyplňte text článku')
                ->setHtmlId('article-form-text');

        $form->addSubmit('save', 'Uložit článek');

        $form->addProtection();


        $form->onValidate[] = [$this, 'checkPublishing'];
        $form->onSuccess[] = [$this, 'processArticleSaving'];

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

    public function processArticleSaving(Form $form, $values)
    {

        $tags = $form->getHttpData(Form::DATA_TEXT, 'tags[]');

        if ($this->article !== null) {
            $article = $this->article;

        } else {
            $article = new Article(
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
            $this->pageFacade->save($article, $values, $tags);
            $this->flashMessage('Článek byl úspěšně uložen.', 'success');

        } catch (ArticleTitleAlreadyExistsException $at) {
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

    public function fillFormBy(Article $article)
    {
        $this['articleForm']['title']->setDefaultValue($article->title);

        if ($article->getPublishedAt() !== null) {
            $this['articleForm']['time']->setDefaultValue($article->publishedAt->format('j.n.Y H:i'));
        }

        $this['articleForm']['isPublished']->setDefaultValue($article->isPublished);
        $this['articleForm']['intro']->setDefaultValue($article->intro);
        $this['articleForm']['text']->setDefaultValue($article->text);
    }
}


interface IArticleFormControlFactory
{
    /**
     * @param User $user
     * @return ArticleFormControl
     */
    public function create(User $user);
}