<?php

namespace Comments\DI;

use App\Extensions\CompilerExtension;

class CommentsExtension extends CompilerExtension
{
    /**
     * Processes configuration data. Intended to be overridden by descendant.
     * @return void
     */
    public function loadConfiguration()
    {
        $cb = $this->getContainerBuilder();

        $this->compiler->parseServices($cb, $this->loadFromFile(__DIR__ . '/config.neon'), $this->name);
    }

}