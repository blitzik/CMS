<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 18.12.2015
 */

namespace App\Components;

class MetaTagsControl extends BaseControl
{
    private $robots = [];
    
    private $metas = [
        'author' => 'Aleš Tichava',
    ];


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/metas.latte');

        $template->metas = $this->metas;

        $template->render();
    }


    public function setRobots($robots)
    {
        if (is_array($robots)) {
            foreach ($robots as $robot) {
                $this->robots[$robot] = $robots;
            }
        } else {
            $this->robots[$robots] = $robots;
        }
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