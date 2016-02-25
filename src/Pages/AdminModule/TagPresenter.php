<?php

namespace Tags\Presenters;

use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Tags\Components\Admin\ITagsOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Tags\Components\Admin\TagsOverviewControl;
use Pages\Factories\TagFormFactory;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
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


    /**
     * @Actions default
     */
    protected function createComponentTagsOverview()
    {
        $comp = $this->tagsOverviewControl->create();
        $comp->onMissingTag[] = [$this, 'onMissingTag'];

        return $comp;
    }


    public function onMissingTag(TagsOverviewControl $control)
    {
        $control->flashMessage('tags.overview.nonExistentTag', 'warning');
        $this->redirect('this');
    }


    /**
     * @Actions default
     */
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

            $this->flashMessage('tags.tagForm.messages.success', 'success', ['name' => $tag->name]);
            $this->redirect('this');

        } catch (TagNameAlreadyExistsException $t) {
            $form->addError($this->translator->translate('tags.tagForm.messages.nameExists', ['name' => $tag->name]));
        } catch (DBALException $e) {
            $form->addError($this->translator->translate('tags.tagForm.messages.savingError'));
        }
    }
}