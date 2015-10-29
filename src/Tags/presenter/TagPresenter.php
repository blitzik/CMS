<?php

namespace Tags\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use App\Exceptions\Runtime\TagNameAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;
use Tags\Components\ITagsOverviewControlFactory;
use Tags\Facades\TagFacade;
use Tags\Tag;
use Tracy\Debugger;

class TagPresenter extends ProtectedPresenter
{
    /**
     * @var ITagsOverviewControlFactory
     * @inject
     */
    public $tagsOverviewControl;

    /**
     * @var TagFacade
     * @inject
     */
    public $tagFacade;

    /** @var  array */
    private $tags;

    public function actionDefault()
    {
        $this->tags = $this->tagFacade->findAllTags(false);
    }

    public function renderDefault()
    {
    }

    protected function createComponentTagsOverview()
    {
        $comp = $this->tagsOverviewControl->create($this->tags);


        return $comp;
    }
    
    protected function createComponentTagCreationForm()
    {
        $form = new Form;

        $form->addText('name', 'Název nového tagu', null, Tag::LENGTH_NAME)
                ->setRequired('Vyplňte název tagu');

        $form->addText('color', 'Barva', null, 7)
                ->setRequired('Přiřaďte novému tagu barvu')
                ->setHtmlId('creation-form-color')
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