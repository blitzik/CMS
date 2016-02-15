<?php

namespace blitzik\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class FormsMacro extends MacroSet
{
    public static function install(Compiler $compiler)
    {
        $set = new static($compiler);
        $set->addMacro('customFormErrors', [$set, 'macroErrors']);
    }

    public function macroErrors(MacroNode $node, PhpWriter $writer)
    {
        // we have variable $form at our disposal in template already.
        // this marco should be used only inside manual rendered form
        return '
            if (isset($form) and $form->hasErrors()) {
                echo \'<div class="row">\';
                    echo \'<div class="form-errors-col">\';
                        echo \'<ul class="form-errors">\';
                        foreach ($form->errors as $error) {
                            echo \'<li class="form-error">
                                       <i class="fa fa-warning"></i> \' .$error. \'</li>\';
                        }
                        echo \'</ul>\';
                    echo \'</div>\';
                echo \'</div>\';
            }
        ';
    }
}