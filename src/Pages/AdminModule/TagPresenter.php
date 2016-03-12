<?php

namespace Tags\Presenters;

use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Tags\Components\Admin\ITagsOverviewControlFactory;
use Tags\Tag;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use App\AdminModule\Presenters\ProtectedPresenter;
use Tags\Components\Admin\TagsOverviewControl;
use blitzik\FlashMessages\FlashMessage;
use Pages\Factories\TagFormFactory;
use Doctrine\DBAL\DBALException;
use Nette\Application\UI\Form;
use Tags\Facades\TagFacade;

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
        $control->flashMessage('tags.overview.nonExistentTag', FlashMessage::WARNING);
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
        try {
            $tag = $this->tagFacade->saveTag((array)$values);

            $this->flashMessage('tags.tagForm.messages.success', FlashMessage::SUCCESS, ['name' => $tag->getName()]);
            $this->redirect('this');

        } catch (TagNameAlreadyExistsException $t) {
            $form->addError($this->translator->translate('tags.tagForm.messages.nameExists', ['name' => $values['name']]));

        } catch (UrlAlreadyExistsException $url) {
            $form->addError($this->translator->translate('tags.tagForm.messages.tagUrlExists'));

        } catch (DBALException $e) {
            $form->addError($this->translator->translate('tags.tagForm.messages.savingError'));
        }
    }
}