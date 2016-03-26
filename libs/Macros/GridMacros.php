<?php

namespace blitzik\Macros;

use Latte\MacroNode;
use Latte\PhpWriter;

class GridMacros extends \Latte\Macros\MacroSet
{
    public static function install(\Latte\Compiler $compiler)
    {
        $set = new static($compiler);

        $set->addMacro('col', [$set, 'macroCol'], [$set, 'macroCoLEnd']);
        $set->addMacro('rowCol', [$set, 'macroRowCol'], [$set, 'macroRowCoLEnd']);
    }


    public function macroCol(MacroNode $node, PhpWriter $writer)
    {
        return $writer->write("
            echo '<div class=%node.word>';
        ");
    }

    
    public function macroColEnd(MacroNode $node, PhpWriter $writer)
    {
        return $writer->write("
            echo '</div>';
        ");
    }


    public function macroRowCol(MacroNode $node, PhpWriter $writer)
    {
        return $writer->write("
            \$rowName = 'row';
            \$args = %node.array;
            if (array_key_exists('row', \$args)) {
                \$rowName = %node.array['row'];
            }
            
            echo '<div class=\"' . \$rowName . '\">';
            echo '<div class=\"' . \$args[0] . '\">';
        ");
    }


    public function macroRowColEnd(MacroNode $node, PhpWriter $writer)
    {
        return "
            echo '</div>';
            echo '</div>';
        ";
    }
}