<?php

namespace Images\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Images\Components\IImageUploadControlFactory;
use Images\Facades\ImageFacade;

class ImagePresenter extends ProtectedPresenter
{
    /**
     * @var IImageUploadControlFactory
     * @inject
     */
    public $imageUploadFactory;

    /**
     * @var ImageFacade
     * @inject
     */
    public $imageFacade;

    public function actionDefault()
    {

    }

    public function renderDefault()
    {

    }

    protected function createComponentImageUpload()
    {
        $comp = $this->imageUploadFactory->create();


        return $comp;
    }

}