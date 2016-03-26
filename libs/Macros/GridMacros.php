<?php

namespace blitzik\Macros;

use Latte\CompileException;
use Latte\MacroNode;
use Latte\PhpWriter;

class GridMacros extends \Latte\Macros\MacroSet
{
    public static function install(\Latte\Compiler $compiler)
    {
        $set = new static($compiler);

        $set->addMacro('row', [$set, 'macroRow'], [$set, 'macroRowEnd']);
        $set->addMacro('col', [$set, 'macroCol'], [$set, 'macroCoLEnd']);
        $set->addMacro('rowCol', [$set, 'macroRowCol'], [$set, 'macroRowCoLEnd']);
    }


    public function macroRow(MacroNode $node, PhpWriter $writer)
    {
        return $writer->write("
            \$args = %node.array;
            \$attributes = null;
            foreach (\$args as \$attributeName => \$attribute) {
                if (!is_numeric(\$attributeName)) {
                    \$attributes .= sprintf(' %s=\"%s\"', \$attributeName, \$attribute);
                }
            }
            echo '<div class=\"' . (!array_key_exists(0, \$args) ? 'row' : \$args[0]) . '\"' . \$attributes . '>';
        ");
    }


    public function macroRowEnd(MacroNode $node, PhpWriter $writer)
    {
        return "echo '</div>'";
    }


    public function macroCol(MacroNode $node, PhpWriter $writer)
    {
        $name = $node->tokenizer->fetchWord();
        if ($name === false) {
            throw new CompileException("Missing Column name in {{$node->name}}.");
        }

        $node->tokenizer->reset();
        return $writer->write("
            \$args = %node.array;
            \$attributes = null;
            foreach (\$args as \$attributeName => \$attribute) {
                if (!is_numeric(\$attributeName)) {
                    \$attributes .= sprintf(' %s=\"%s\"', \$attributeName, \$attribute);
                }
            }
            echo '<div class=\"' . %node.word . '\"' . \$attributes . '>';
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
        $name = $node->tokenizer->fetchWord();
        if ($name === false) {
            throw new CompileException("Missing Column name in {{$node->name}}.");
        }

        $node->tokenizer->reset();
        return $writer->write("
            \$rowClassName = 'row';
            \$args = %node.array;
            if (array_key_exists('row', \$args)) {
                \$rowClassName = \$args['row'];
            }
            
            echo '<div class=\"' . \$rowClassName . '\">';
            echo '<div class=\"' .%node.word. '\">';
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