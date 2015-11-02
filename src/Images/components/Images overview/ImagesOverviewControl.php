<?php

namespace Images\Components;

use App\BaseControl;
use Images\Facades\ImageFacade;

class ImagesOverviewControl extends BaseControl
{
    /** @var ImageFacade  */
    private $imageFacade;

    public function __construct(
        ImageFacade $imageFacade
    ) {

        $this->imageFacade = $imageFacade;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/imagesOverview.latte');



        $template->render();
    }
}


interface IImagesOverviewControlFactory
{
    /**
     * @return ImagesOverviewControl
     */
    public function create();
}