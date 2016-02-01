<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.01.2016
 */

namespace Navigations;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="category",
 *     indexes={
 *          @Index(name="lft", columns={"lft"})
 *     }
 * )
 *
 */
class Category
{
    use Identifier;
    use MagicAccessors;

    const NAME_LENGTH = 255;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Navigation")
     * @ORM\JoinColumn(name="navigation", referencedColumnName="id", nullable=false)
     * @var Navigation
     */
    private $navigation;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", nullable=true)
     * @var Category
     */
    private $parent;

    /**
     * @ORM\Column(name="lft", type="integer", nullable=false, unique=false)
     * @var int
     */
    protected $lft;

    /**
     * @ORM\Column(name="rgt", type="integer", nullable=false, unique=false)
     * @var int
     */
    protected $rgt;

    /**
     * @ORM\Column(name="lvl", type="integer", nullable=false, unique=false)
     * @var int
     */
    protected $lvl;


    public function __construct(
        $name,
        $parent,
        Navigation $navigation,
        $lft,
        $rgt,
        $depth
    ) {
        $this->setName($name);
        $this->setParent($parent);
        $this->navigation = $navigation;
        $this->setLft($lft);
        $this->setRgt($rgt);
        $this->setLvl($depth);
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        Validators::assert($name, 'unicode:1..' . self::NAME_LENGTH);
        $this->name = $name;
    }


    /**
     * @param Category|null $parent
     */
    public function setParent($parent)
    {
        if (!$parent instanceof Category) {
            Validators::assert($parent, 'null');
        }
        $this->parent = $parent;
    }


    /**
     * @param int $lft
     */
    public function setLft($lft)
    {
        Validators::assert($lft, 'numericint');
        $this->lft = $lft;
    }


    /**
     * @param int $rgt
     */
    public function setRgt($rgt)
    {
        Validators::assert($rgt, 'numericint');
        $this->rgt = $rgt;
    }


    /**
     * @param int $lvl
     */
    public function setLvl($lvl)
    {
        Validators::assert($lvl, 'numericint');
        $this->lvl = $lvl;
    }


    public function getNavigationName()
    {
        return $this->navigation->name;
    }

}