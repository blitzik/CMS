<?php

namespace Pages\Components;

use App\Exceptions\LogicExceptions\InvalidArgumentException;
use Tags\Facades\TagFacade;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use App\BaseControl;
use Pages\Article;

class ArticleTagsPickingControl extends BaseControl
{
    /** @var TagFacade  */
    private $tagFacade;

    /** @var Article|null  */
    private $article;

    public function __construct(
        $article,
        TagFacade $tagFacade
    ) {
        if ($article !== null and !$article instanceof Article) {
            throw new InvalidArgumentException('Only instances of ' .Article::class. ' or NULL is allowed');
        }

        $this->article = $article;
        $this->tagFacade = $tagFacade;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/articleTagsPicking.latte');

        $template->tags = ArrayHash::from(Arrays::associate($this->tagFacade->findAllTags(), 'id'));
        $template->articleTags = isset($this->article) ? $this->article->getTags() : [];

        $template->render();
    }
}


interface IArticleTagsPickingControlFactory
{
    /**
     * @param Article|null $article
     * @return ArticleTagsPickingControl
     */
    public function create($article);
}