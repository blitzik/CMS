<?php

namespace App\AdminModule\Presenters;

use App\Presenters\AppPresenter;

abstract class ProtectedPresenter extends AppPresenter
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