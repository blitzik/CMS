<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 12.01.2016
 */

namespace Navigations;

use Nette\Object;

class TreeBuilder extends Object
{
    /**
     * @param array $nodes
     * @return Node
     * @throws \Exception
     */
    public function buildTree(array $nodes)
    {
        $categorizedNodes = $this->categorizeNodesByLevel($nodes);
        unset($nodes);
        $this->resolveRelationships($categorizedNodes);

        $rootNode = $categorizedNodes[0];
        unset($categorizedNodes);

        return $rootNode; // 0; returns root node (lvl = 0)
    }


    /**
     * @param array $nodes
     * @return array
     */
    private function categorizeNodesByLevel(array $nodes)
    {
        $nodesByLevel = [];
        /** @var array $node */
        foreach ($nodes as $node) {
            $nodesByLevel[$node['lvl']][$node['id']] = new Node($node);
        }

        return $nodesByLevel;
    }


    /**
     * @param array $nodes
     * @return array
     * @throws \Exception
     */
    private function resolveRelationships(array &$nodes)
    {
        $lvl = max(array_keys($nodes)); // max lvl (depth)
        while ($lvl > 0) {
            /** @var Node $childNode */
            foreach ($nodes[$lvl] as $childNode) {
                /** @var Node $parentNode */
                foreach ($nodes[$lvl - 1] as $parentNode) {
                    $childNode->addNode($parentNode);
                }
            }

            $lvl--;
        }
    }
}