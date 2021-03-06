<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.12.2015
 */

namespace App\Components;

class MetaTagsControl extends BaseControl
{
    /** @var array */
    private $metas = [];


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/metas.latte');

        $template->metas = $this->metas;

        $template->render();
    }


    public function addMeta($name, $content)
    {
        if (!empty($content)) {
            $this->metas[$name] = $content;
        }
    }
}


interface IMetaTagsControlFactory
{
    /**
     * @return MetaTagsControl
     */
    public function create();
}