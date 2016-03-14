<?php

namespace Tags\Presenters;

use Pages\Exceptions\Runtime\TagNameAlreadyExistsException;
use Tags\Components\Admin\ITagsOverviewControlFactory;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use App\AdminModule\Presenters\ProtectedPresenter;
use Tags\Components\Admin\TagsOverviewControl;
use Pages\Components\ITagFormControlFactory;
use blitzik\FlashMessages\FlashMessage;
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

        return $comp;
    }
}