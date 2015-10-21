<?php

namespace App\FrontModule\Presenters;

use Nette;


class BasePresenter extends \App\Presenters\BasePresenter
{
    /** @persistent */
    //public $lang;

    public function findLayoutTemplateFile()
    {
        if ($this->layout === FALSE) {
            return;
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . '@layout.latte';
    }

}
