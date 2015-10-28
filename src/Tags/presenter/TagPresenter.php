<?php

namespace Tags\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use App\Exceptions\Runtime\TagNameAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;
use Tags\Facades\TagFacade;
use Tags\Tag;

class TagPresenter extends ProtectedPresenter
{
    /**
     * @var TagFacade
     * @inject
     */
    public $tagFacade;

    /** @var  array */
    private $tags;

    public function actionDefault()
    {
        $this->tags = $this->tagFacade->findAllTags();
    }

    public function renderDefault()
    {
        $this->template->tags = ArrayHash::from($this->tags);
    }

    protected function createComponentTagsForm()
    {
        $form = new Form;

        foreach ($this->tags as $tag) {
            $form->addText('color'.$tag['id'], null, null, 7)
                    ->setDefaultValue($tag['color'])
                    ->addRule(Form::PATTERN, 'Špatný formát barvy.', '^#([0-f]{3}|[0-f]{6})$');

            $form->addSubmit('tag'.$tag['id'], 'Uložit')
                    ->onClick[] = function (SubmitButton $button) use ($tag){
                        $this->processTag($button, $tag);
                    };
        }

        $form->addProtection();

        return $form;
    }

    public function processTag(SubmitButton $button, $tag)
    {
        $values = $button->getForm()->getValues();

        $this->tagFacade->changeColor($tag['id'], $values['color' . $tag['id']]);

        $this->redirect('this#tag-'.$tag['id']);
    }

    public function handleRemoveTag($id)
    {
        $this->tagFacade->removeTag($id);

        $this->flashMessage('Tag byl úspěšně smazán', 'success');
        $this->redirect('this');
    }
    
    protected function createComponentTagCreationForm()
    {
        $form = new Form;

        $form->addText('name', 'Název nového tagu', null, Tag::LENGTH_NAME)
                ->setRequired('Vyplňte název tagu');

        $form->addText('color', 'Barva', null, 7)
                ->setRequired('Přiřaďte novému tagu barvu')
                ->addRule(Form::PATTERN, 'Špatný formát barvy.', '^#([0-f]{3}|[0-f]{6})$');;

        $form->addSubmit('save', 'Uložit tag');

        $form->onSuccess[] = [$this, 'processNewTag'];

        return $form;
    }

    public function processNewTag(Form $form, $values)
    {
        $tag = new Tag($values->name, $values->color);

        try {
            $this->tagFacade->saveTag($tag);

            $this->flashMessage('Tag byl úspěšně přidán', 'success');
            $this->redirect('this');

        } catch (TagNameAlreadyExistsException $t) {
            $form->addError('Tag s tímto názvem již existuje');
        } catch (DBALException $e) {
            $form->addError('Při vytvážení tagu nastala chyba');
        }
    }
}