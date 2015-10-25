<?php

namespace Dashboard;

use App\Extensions\CompilerExtension;

class DashboardExtension extends CompilerExtension
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();
        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon', $this->name));
    }


    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function beforeCompile()
    {
        $cb = $this->getContainerBuilder();
        $this->setPresenterMapping($cb, ['Dashboard' => 'Dashboard\\*Module\\Presenters\\*Presenter']);
    }

}