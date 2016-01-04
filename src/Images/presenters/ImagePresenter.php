<?php

namespace Images\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Images\Components\IImagesOverviewControlFactory;
use Images\Components\IImageUploadControlFactory;
use Images\Facades\ImageFacade;

class ImagePresenter extends ProtectedPresenter
{
    /**
     * @var IImagesOverviewControlFactory
     * @inject
     */
    public $imagesOverviewFactory;

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


    protected function createComponentImagesOverview()
    {
        $comp = $this->imagesOverviewFactory->create();

        return $comp;
    }

}