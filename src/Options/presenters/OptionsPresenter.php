<?php

namespace Options\Presenters;

use Options\Components\Admin\IOptionsFormControlFactory;
use App\AdminModule\Presenters\ProtectedPresenter;

class OptionsPresenter extends ProtectedPresenter
{
    /**
     * @var IOptionsFormControlFactory
     * @inject
     */
    public $optionsFormControlFactory;


    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('options.title');
    }


    public function renderDefault()
    {
    }


    /**
     * @Actions default
     */
    protected function createComponentOptionsForm()
    {
        $comp = $this->optionsFormControlFactory->create();

        return $comp;
    }
}