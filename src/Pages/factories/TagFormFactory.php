<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 22.02.2016
 */

namespace Pages\Factories;

use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Object;
use Tags\Tag;

class TagFormFactory extends Object
{
    /** @var ITranslator */
    private $translator;


    public function __construct(ITranslator $translator = null)
    {
        $this->translator = $translator;
    }


    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('tags.tagForm'));

        $form->addText('name', 'name.label', null, Tag::LENGTH_NAME)
                ->setRequired('name.messages.required');

        $form->addText('color', 'color.label', null, 7)
                ->setRequired('color.messages.required')
                ->setDefaultValue('#')
                ->addRule(Form::PATTERN, 'color.messages.wrongPattern', '^#([0-f]{3}|[0-f]{6})$');;

        $form->addSubmit('save', 'save.caption');

        return $form;
    }
}