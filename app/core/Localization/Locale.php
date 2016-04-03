<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 13.03.2016
 */

namespace Localization;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="locale")
 *
 */
class Locale
{
    use Identifier;

    /**
     * @ORM\Column(name="name", type="string", length=5, nullable=false, unique=true)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="code", type="string", length=2, nullable=false, unique=false)
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(name="lang", type="string", length=25, nullable=false, unique=false)
     * @var string
     */
    private $lang;

    /**
     * @ORM\Column(name="`default`", type="boolean", nullable=false, unique=false, options={"default": false})
     * @var boolean
     */
    private $default;


    public function __construct($name, $code, $lang, $default = false)
    {
        $this->setName($name);
        $this->setCode($code);
        $this->setLang($lang);

        $this->default = (bool)$default;
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
        Validators::assert($name, 'unicode:1..5');
        $this->name = $name;
    }


    /**
     * @param string $code
     */
    private function setCode($code)
    {
        Validators::assert($code, 'unicode:1..2');
        $this->code = $code;
    }


    /**
     * @param string $lang
     */
    private function setLang($lang)
    {
        Validators::assert($lang, 'unicode:1..25');
        $this->lang = $lang;
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
    public function getCode()
    {
        return $this->code;
    }


    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }


    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }


}