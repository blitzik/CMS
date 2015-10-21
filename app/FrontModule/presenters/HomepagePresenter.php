<?php

namespace App\FrontModule\Presenters;

use Kdyby\Doctrine\EntityManager;
use Pages\Article;

class HomepagePresenter extends BasePresenter
{
    /**
     * @var EntityManager
     * @inject
     */
    public $em;

    public function actionDefault()
    {

    }

    public function renderDefault()
    {
        $this->template->articles = $this->em->createQuery(
            'SELECT a, aa FROM ' .Article::class. ' a
             JOIN a.author aa
             WHERE a.isPublished = true
             ORDER BY a.publishedAt DESC'
        )->getResult();
    }
}