<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 20.02.2016
 */

namespace App\Components;

class PageTitleControl extends BaseControl
{
    /** @var string */
    private $defaultTitle;

    /** @var string */
    private $pageTitle;

    /** @var string */
    private $joinedTitleText;


    public function __construct($defaultTitle)
    {
        $this->defaultTitle = $defaultTitle;
    }


    /**
     * @param $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }


    /**
     * @param $joinedTitleText
     * @return $this
     */
    public function joinTitleText($joinedTitleText)
    {
        $this->joinedTitleText = $joinedTitleText;

        return $this;
    }


    public function render($entireTitle = true)
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/title.latte');

        $this->template->title = $this->pageTitle;
        $this->template->joinedTitleText = $this->joinedTitleText;
        $this->template->defaultTitle = $this->defaultTitle;
        $this->template->entireTitle = $entireTitle;

        $template->render();
    }
}


interface IPageTitleControlFactory
{
    /**
     * @param string $defaultTitle
     * @return PageTitleControl
     */
    public function create($defaultTitle);
}