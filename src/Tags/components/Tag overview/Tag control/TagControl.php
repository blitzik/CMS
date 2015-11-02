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

        $form->getElementPrototype()->id = 'form-tag-'.$this->tag['id'];

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

            if ($this->presenter->isAjax()) {
                $this->tag['color'] = $values->color;
                $this->redrawControl();
            } else {
                $this->redirect('this#tag-' . $this->tag['id']);
            }

        } catch (DBALException $e) {
            $this->flashMessage('Změnu pro tag ['.$this->tag['name'].'] se nepodařilo uložit', 'error');
            if ($this->presenter->isAjax()) {
                $this->redrawControl('flashes');
            } else {
                $this->redirect('this');
            }
        }
    }

    public function handleRemoveTag($id)
    {
        try {
            $this->tagFacade->removeTag($id);

            if ($this->presenter->isAjax()) {
                $this->redrawControl('tag');
            } else {
                $this->redirect('this');
            }
        } catch (DBALException $e) {
            $this->flashMessage('Při odstraňování tagu nastala chyba.', 'error');
            if ($this->presenter->isAjax()) {
                // value does not matter, in JS we just check existence of this variable
                $this->presenter->payload->errorEl = true;
                $this->redrawControl('flashes');
            } else {
                $this->redirect('this');
            }
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