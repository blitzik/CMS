<?php

namespace Tags;

use Pages\Exceptions\Logic\InvalidArgumentException;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Security\IResource;
use Nette\Utils\Validators;
use Url\Url;

/**
 * @ORM\Entity
 * @ORM\Table(name="tag")
 */
class Tag implements IResource
{
    use Identifier;

    const LENGTH_NAME = 30;


    /**
     * @ORM\Column(name="name", type="string", length=30, nullable=false, unique=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="color", type="string", length=7, nullable=false, unique=false)
     * @var string
     */
    private $color;


    public function __construct(
        $name,
        $color
    ) {
        $this->setName($name);
        $this->setColor($color);
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */

    /**
     * @param string $name
     */
    private function setName($name)
    {
        Validators::assert($name, 'unicode:1..'.self::LENGTH_NAME);
        $this->name = $name;
    }

    /**
     * @param string $color Color in HEX format
     */
    public function setColor($color)
    {
        if (!preg_match('~^#([0-f]{3}|[0-f]{6})$~', $color)) {
            throw new InvalidArgumentException('wrong format of color. Only HEX format can pass');
        }

        $this->color = $color;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    public function getName()
    {
        return $this->name;
    }


    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }


    /*
     * ---------------------
     * ----- I_RESOURCE -----
     * ---------------------
     */


    function getResourceId()
    {
        return 'page_tag';
    }

}