<?php

namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;

abstract class ProtectedPresenter extends BasePresenter
{
    /**
     * Finds layout template file name.
     * @return string
     * @internal
     */
    public function findLayoutTemplateFile()
    {
        return __DIR__ . '/templates/@layout.latte';
    }

}