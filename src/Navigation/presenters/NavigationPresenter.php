<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.01.2016
 */

namespace Navigations\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Navigations\Controls\INavigationControlFactory;
use Navigations\NavigationFacade;

class NavigationPresenter extends ProtectedPresenter
{
    /**
     * @var INavigationControlFactory
     * @inject
     */
    public $navigationControlFactory;

    /**
     * @var NavigationFacade
     * @inject
     */
    public $navigationFacade;


    public function actionDefault()
    {

    }


    public function renderDefault()
    {

    }


    protected function createComponentNavigation()
    {
        $comp = $this->navigationControlFactory->create(1);

        return $comp;
    }
}