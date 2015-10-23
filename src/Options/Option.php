<?php

namespace Options;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
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
    use MagicAccessors;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="value", type="string", length=255, nullable=false, unique=false)
     * @var string
     */
    protected $value;

    public function __construct(
        $name,
        $value
    ) {
        $this->setName($name);
        $this->setValue($value);
    }

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
        Validators::assert($value, 'unicode:1..255');
        $this->value = $value;
    }

    public static function getCacheKey()
    {
        return self::class;
    }
}