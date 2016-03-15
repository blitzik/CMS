<?php

namespace Tags\Presenters;

use Tags\Components\Admin\ITagsOverviewControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;
use Tags\Components\Admin\TagsOverviewControl;
use Pages\Components\ITagFormControlFactory;
use blitzik\FlashMessages\FlashMessage;
use Pages\Components\TagFormControl;
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
     * @var ITagFormControlFactory
     * @inject
     */
    public $tagFormControlFactory;

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
        $comp = $this->tagFormControlFactory->create();
        $comp->onSuccessTagSaving[] = [$this, 'onSuccessTagSaving'];

        return $comp;
    }


    public function onSuccessTagSaving(Tag $tag, TagFormControl $control)
    {
        $control->flashMessage('tags.tagForm.messages.success', FlashMessage::SUCCESS, ['name' => $tag->getName()]);
        $control->redirect('this');
    }
}