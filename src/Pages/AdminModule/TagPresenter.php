<?php

namespace Tags\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Kdyby\Translation\Translator;
use Nette\Localization\ITranslator;
use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Pages\Factories\TagFormFactory;
use Tags\Components\ITagsOverviewControlFactory;
use Tags\Components\TagsOverviewControl;
use Tags\Facades\TagFacade;
use Tags\Tag;

class TagPresenter extends ProtectedPresenter
{
    /**
     * @var ITagsOverviewControlFactory
     * @inject
     */
    public $tagsOverviewControl;

    /**
     * @var TagFormFactory
     * @inject
     */
    public $tagFormFactory;

    /**
     * @var TagFacade
     * @inject
     */
    public $tagFacade;


    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('tags.title');
    }


    public function renderDefault()
    {
    }


    protected function createComponentTagsOverview()
    {
        $comp = $this->tagsOverviewControl->create();
        $comp->onMissingTag[] = [$this, 'onMissingTag'];

        return $comp;
    }


    public function onMissingTag(TagsOverviewControl $control)
    {
        $control->flashMessage('Požadovanou akci nelze vykonat nad neexistujícím Tagem', 'warning');
        $this->redirect('this');
    }


    protected function createComponentTagCreationForm()
    {
        $form = $this->tagFormFactory->create();

        $form['color']->setHtmlId('creation-form-color');

        $form->onSuccess[] = [$this, 'processNewTag'];

        return $form;
    }


    public function processNewTag(Form $form, $values)
    {
        $tag = new Tag($values->name, $values->color);

        try {
            $this->tagFacade->saveTag($tag);

            $this->flashMessage('tags.tagForm.messages.success', 'success', null, ['name' => $tag->name]);
            $this->redirect('this');

        } catch (TagNameAlreadyExistsException $t) {
            $form->addError($this->translator->translate('tags.tagForm.messages.nameExists', null, ['name' => $tag->name]));
        } catch (DBALException $e) {
            $form->addError($this->translator->translate('tags.tagForm.messages.savingError'));
        }
    }
}