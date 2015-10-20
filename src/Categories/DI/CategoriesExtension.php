<?php

namespace Categories;

use App\Extensions\CompilerExtension;

class CategoriesExtension extends CompilerExtension
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Categories' => 'Categories\\*Module\\Presenters\\*Presenter']);
    }

}