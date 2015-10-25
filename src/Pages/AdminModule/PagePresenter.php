<?php

namespace Pages\AdminModule\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use App\Exceptions\Runtime\ArticleTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Pages\Article;
use Pages\Facades\PageFacade;
use Tags\Facades\TagFacade;

class PagePresenter extends ProtectedPresenter
{
    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /**
     * @var TagFacade
     * @inject
     */
    public $tagFacade;

    /** @var  Article */
    private $article;

    /** @var  array */
    private $tags;

    /*
     * ----------------------------
     * ----- ARTICLE CREATION -----
     * ----------------------------
     */

    public function actionDefault($id)
    {
        $this->tags = Arrays::associate($this->tagFacade->findAllTags(), 'id');
    }

    public function renderDefault($id)
    {
        $this->template->tags = ArrayHash::from($this->tags);
    }

    protected function createComponentArticleForm()
    {
        $form = new Form;

        $form->addText('title', 'Titulek (*)', null, Article::LENGTH_TITLE)
                ->setRequired('Vyplňte titulek článku')
                ->addRule(Form::MAX_LENGTH, 'Titulek článku může obsahovat pouze %d znaků', Article::LENGTH_TITLE);

        $form->addText('time', 'Datum publikování článku', null, 16)
                ->setHtmlId('datetimepicker')
                ->setRequired('Nastavte datum publikování článku')
                ->addRule(Form::MAX_LENGTH, 'Neplatná délka řetězce (publikování článku)', 16);

        $form->addCheckbox('isDraft', 'Článek nepublikovat')
                ->setDefaultValue(true);

        $form->addTextArea('intro', 'Úvodní text článku (*)', null, 7)
                ->setMaxLength(Article::LENGTH_INTRO)
                ->setRequired('Vyplňte text úvodu článku')
                ->addRule(Form::MAX_LENGTH, 'Úvod článku může obsahovat pouze %d znaků', Article::LENGTH_INTRO);

        $form->addTextArea('text', 'Text článku (*)', null, 25)
                ->setRequired('Vyplňte text článku');

        $form->addSubmit('save', 'Uložit článek');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onArticleSaving'];

        return $form;
    }

    public function onArticleSaving(Form $form, $values)
    {
        $tags = array_flip($form->getHttpData(Form::DATA_TEXT, 'tags[]'));

        $article = new Article(
            $values->title,
            $values->intro,
            $values->text,
            $this->userEntity
        );

        if (!empty($tags)) {
            $tags = array_intersect_key($this->tags, $tags);
        }

        if ($values->isDraft == false) {
            try {
                $publishTime = new \DateTime($values->time);
            } catch (\Exception $e) {
                $form->addError('Špatný formát data. Opravte datum v poli Publikování článku.');
                return;
            }

            $article->publish($publishTime);
        }

        try {
            $this->pageFacade->save($article, $tags);
        } catch (ArticleTitleAlreadyExistsException $at) {
            $form->addError('Článek s tímto titulkem již existuje');
            return;
        } catch (UrlAlreadyExistsException $ur) {
            $form->addError('Vámi zadaná Url již existuje');
            return;
        } catch (DBALException $e) {
            $form->addError('Při ukládání došlo k chybě');
            return;
        }

        $this->flashMessage('Článek byl úspěšně uložen.');
        $this->redirect('this');

    }

    /*
     * ---------------------------
     * ----- ARTICLE EDITING -----
     * ---------------------------
     */

    public function actionEdit($id)
    {
        $this->article = $this->pageFacade->findOneBy(['id' => intval($id)]);
        if ($this->article === null) {
            $this->flashMessage('Požadovaný článek nebyl nalezen.');
            $this->redirect(':Dashboard:Dashboard:default');
        }
    }

    public function renderEdit($id)
    {

    }

}