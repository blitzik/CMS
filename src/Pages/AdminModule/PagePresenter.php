<?php

namespace Pages\AdminModule\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use App\Exceptions\Runtime\ArticleTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Pages\Article;
use Pages\Facades\PageFacade;

class PagePresenter extends ProtectedPresenter
{
    /**
     * @var PageFacade
     * @inject
     */
    public $pageFacade;

    /** @var  Article */
    private $article;

    /*
     * ----------------------------
     * ----- ARTICLE CREATION -----
     * ----------------------------
     */

    public function actionDefault($id)
    {

    }

    public function renderDefault($id)
    {

    }

    protected function createComponentArticleForm()
    {
        $form = new Form;

        $form->addText('title', 'Titulek', null, 255)
                ->setRequired('Vyplňte titulek článku');

        $form->addTextArea('text', 'Text', null, 25)
                ->setRequired('Vyplňte text článku');

        $form->addSubmit('save', 'Uložit');

        $form->addProtection();

        $form->onSuccess[] = [$this, 'onArticleSaving'];

        return $form;
    }

    public function onArticleSaving(Form $form, $values)
    {
        $article = new Article($values->title, $values->text, $this->userEntity);

        try {
            $this->pageFacade->save($article);
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