<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 15.01.2016
 */

namespace Navigations\Controls;

use App\Components\BaseControl;
use Navigations\NavigationFacade;

class NavigationControl extends BaseControl
{
    /** @var NavigationFacade */
    private $navigationFacade;

    /** @var int */
    private $navigationId;


    public function __construct(
        $navigationId,
        NavigationFacade $navigationFacade
    ) {
        $this->navigationId = $navigationId;
        $this->navigationFacade = $navigationFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/navigation.latte');

        $template->node = $this->navigationFacade
                               ->getEntireNavigation($this->navigationId);

        $template->render();
    }
}


interface INavigationControlFactory
{
    /**
     * @param int $navigationId
     * @return NavigationControl
     */
    public function create($navigationId);
}