<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 12.01.2016
 */

namespace Navigations;

use Nette\Object;

class Node extends Object
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var int */
    private $lvl;

    /** @var int */
    private $lft;

    /** @var  int */
    private $rgt;

    /** @var Node */
    private $parent;

    /** @var Node[] */
    private $children = [];


    public function __construct(array $nodeParams)
    {
        $this->id   = $nodeParams['id'];
        $this->name = $nodeParams['name'];
        $this->lvl  = $nodeParams['lvl'];
        $this->lft  = $nodeParams['lft'];
        $this->rgt  = $nodeParams['rgt'];
    }


    public function addNode(Node $node)
    {
        if ($this->getLvl() === $node->getLvl()) { // siblings
            throw new \Exception('Only parent or child nodes can be added'); // todo
        }

        if ($this->getLvl() < $node->getLvl()) {
            $this->addAsChild($node);
            return;
        }

        if ($this->getLvl() > $node->getLvl()) {
            $this->addAsParent($node);
            return;
        }
    }


    /**
     * @param Node $childNode
     */
    private function addAsChild(Node $childNode)
    {
        // $this => parent node
        if ($this->lft < $childNode->getLft() and $this->rgt > $childNode->getRgt()) {
            $this->children[$childNode->getId()] = $childNode;
        }
    }


    /**
     * @param Node $parentNode
     * @throws \Exception
     */
    private function addAsParent(Node $parentNode)
    {
        // $this => child node
        if ($this->lft > $parentNode->getLft() and $this->rgt < $parentNode->getRgt()) {
            $this->parent = $parentNode;
            $parentNode->addNode($this);
        }
    }


    /**
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }


    /**
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * @return Node|null
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * @param bool $fromRoot
     * @return Node[]
     */
    public function getParents($fromRoot = false)
    {
        if ($this->parent === null) {
            return [];
        }

        $parents = $this->getParentNodes([$this->parent]);
        if ($fromRoot === true) {
            return array_reverse($parents);
        }

        return $parents;
    }


    /**
     * @param array $parents
     * @return array
     */
    private function getParentNodes(array $parents)
    {
        end($parents);

        /** @var Node $lastParent */
        $lastParent = $parents[key($parents)];
        if ($lastParent->getParent() !== null) {
            $parents[$lastParent->getParent()->getId()] = $lastParent->getParent();
            $parents = $this->getParentNodes($parents);
        }

        return $parents;
    }


    /**
     * Returns number of immediate children
     *
     * @return int
     */
    public function getChildrenCount()
    {
        return count($this->children);
    }


    /**
     * Returns number of children from all levels
     *
     * @return int
     */
    public function getChildrenTotalCount()
    {
        return (int)(($this->rgt - $this->lft) / 2);
    }


    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return int
     */
    public function getLvl()
    {
        return (int)$this->lvl;
    }


    /**
     * @return int
     */
    public function getLft()
    {
        return (int)$this->lft;
    }


    /**
     * @return int
     */
    public function getRgt()
    {
        return (int)$this->rgt;
    }

}