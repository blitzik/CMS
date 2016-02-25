<?php

namespace Images\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Images\Components\IImagesFilterControlFactory;
use Images\Components\IImagesOverviewControlFactory;
use Images\Components\IImageUploadControlFactory;
use Images\Facades\ImageFacade;
use Images\Query\ImageQuery;

class ImagePresenter extends ProtectedPresenter
{
    /**
     * @var IImagesFilterControlFactory
     * @inject
     */
    public $imagesFilterControlFactory;

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


    // ----


    /** @persistent */
    public $name;

    /** @persistent */
    public $extension;

    /** @persistent */
    public $maxWidth;

    /** @persistent */
    public $maxHeight;


    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('images.title');

        $this['filter-form']['name']->setDefaultValue($this->name);
        $this['filter-form']['extension']->setDefaultValue($this->extension);
        $this['filter-form']['maxWidth']->setDefaultValue($this->maxWidth);
        $this['filter-form']['maxHeight']->setDefaultValue($this->maxHeight);
    }


    public function renderDefault()
    {
    }


    /**
     * @Actions default
     */
    protected function createComponentImageUpload()
    {
        $comp = $this->imageUploadFactory->create();

        return $comp;
    }


    /**
     * @Actions default
     */
    protected function createComponentFilter()
    {
        $comp = $this->imagesFilterControlFactory->create();

        return $comp;
    }


    /**
     * @Actions default
     */
    protected function createComponentImagesOverview()
    {
        $comp = $this->imagesOverviewFactory
                     ->create(
                         (new ImageQuery())
                         ->byName($this->name)
                         ->byExtension($this->extension)
                         ->maxWidth($this->maxWidth)
                         ->maxHeight($this->maxHeight)
                     );

        return $comp;
    }

}