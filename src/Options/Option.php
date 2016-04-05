<?php

namespace Options;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="`option`")
 *
 */
class Option
{
    use Identifier;

    const CACHE_NAMESPACE = 'blog_options';
    
    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="value", type="string", length=255, nullable=true, unique=false)
     * @var string
     */
    private $value;

    public function __construct(
        $name,
        $value
    ) {
        $this->setName($name);
        $this->setValue($value);
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    /**
     * @param string $name
     */
    public function setName($name)
    {
        Validators::assert($name, 'unicode:1..255');
        $this->name = $name;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        Validators::assert($value, 'unicode:..255|null');
        if ($value === '') {
            $value = null;
        }

        $this->value = $value;
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */
    

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }




    public static function getCacheKey()
    {
        return self::class;
    }
}