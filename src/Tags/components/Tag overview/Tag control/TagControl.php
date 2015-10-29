<?php

namespace Tags\Components;

use App\BaseControl;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tags\Facades\TagFacade;
use Tracy\Debugger;

class TagControl extends BaseControl
{
    /** @var array */
    public $onTagRemoval = [];
    public $onColorChange = [];

    /** @var TagFacade  */
    private $tagFacade;

    /** @var array  */
    private $tag;

    public function __construct(
        array $tag,
        TagFacade $tagFacade
    ) {
        $this->tagFacade = $tagFacade;
        $this->tag = $tag;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/tag.latte');

        $template->tag = ArrayHash::from($this->tag);

        $template->render();
    }

    protected function createComponentTagForm()
    {
        $form = new Form;

        $form->addText('color', null, null, 7)
                ->setHtmlId('tag-color-input-'.$this->tag['id'])
                ->setDefaultValue($this->tag['color'])
                ->addRule(Form::PATTERN, 'Špatný formát barvy.', '^#([0-f]{3}|[0-f]{6})$');

        $form->addSubmit('save', 'Uložit')
                ->setHtmlId('tag-submit-'.$this->tag['id']);

        $form->onSuccess[] = [$this, 'processTag'];

        return $form;
    }

    public function processTag(Form $form, $values)
    {
        try {
            $this->tagFacade->changeColor($this->tag['id'], $values->color);
            $this->flashMessage('Barva tagu ['.$this->tag['name'].'] byla úspěšně změněna', 'success');

            if ($this->presenter->isAjax()) {
                $this->tag['color'] = $values->color;
                $this->redrawControl();
            } else {
                $this->redirect('this#tag-' . $this->tag['id']);
            }

        } catch (DBALException $e) {
            $form->addError('Změnu pro tag ['.$this->tag['name'].'] se nepodařilo uložit');
        }
    }

    public function handleRemoveTag($id)
    {
        $this->tagFacade->removeTag($id);

        if ($this->presenter->isAjax()) {
            $this->redrawControl();
            $this->onTagRemoval($id);
        } else {
            $this->redirect('this');
        }
    }
}


interface ITagControlFactory
{
    /**
     * @param array $tag
     * @return TagControl
     */
    public function create(array $tag);
}