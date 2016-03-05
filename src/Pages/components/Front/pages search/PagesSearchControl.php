<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 26.02.2016
 */

namespace Pages\Components\Front;

use App\Components\BaseControl;
use blitzik\FlashMessages\FlashMessage;
use Nette\Application\UI\Form;
use Tags\Facades\TagFacade;
use Tags\Query\TagQuery;
use Tags\Tag;

class PagesSearchControl extends BaseControl
{
    /** @var TagFacade */
    private $tagFacade;

    /** @var Tag[] */
    private $tags;

    /** @var array */
    private $selectedTags = [];


    public function __construct(
        array $tags,
        TagFacade $tagFacade
    ) {
        $this->tagFacade = $tagFacade;
        $this->tags = $tags;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/pagesSearch.latte');

        $template->tags = $this->tags;
        $template->selectedTags = array_flip($this->selectedTags);

        $template->render();
    }


    /**
     * @param array $selectedTags
     */
    public function setSelectedTags(array $selectedTags)
    {
        $this->selectedTags = array_intersect(array_keys($this->tags), array_unique($selectedTags));
    }

    
    protected function createComponentForm()
    {
        $form = new Form();

        $form->addSubmit('search', 'Vyhledat');

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }


    public function processForm(Form $form, $values)
    {
        $selectedTags = array_flip($form->getHttpData(Form::DATA_TEXT, 'tags[]'));

        if (empty($selectedTags)) {
            $this->flashMessage('Vyberte štítky podle kterých chcete vyhledávat', FlashMessage::WARNING);
            $this->presenter->redirect('this', ['tags' => null]);
        }

        $tags = '';
        foreach ($selectedTags as $id => $val) {
            if (array_key_exists($id, $this->tags)) {
                $tags .= $id . '-';
            }
        }

        $tags = mb_substr($tags, 0, mb_strlen($tags) - 1);

        $this->presenter
             ->redirect('this', ['tags' => $tags]);
    }

}


interface IPagesSearchControlFactory
{
    /**
     * @param array $tags
     * @return PagesSearchControl
     */
    public function create(array $tags);
}