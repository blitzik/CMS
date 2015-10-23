<?php

namespace Pages\Components;

use Nette\Utils\ArrayHash;
use App\BaseControl;

class ArticleControl extends BaseControl
{
    /** @var ArrayHash  */
    private $article;

    public function __construct(ArrayHash $article)
    {
        $this->article = $article;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/article.latte');

        $template->article = $this->article;
        $template->month = $this->article->publishedAt->format('n');

        $template->render();
    }
}


interface IArticleControlFactory
{
    /**
     * @param ArrayHash $article
     * @return ArticleControl
     */
    public function create(ArrayHash $article);
}