<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 23.02.2016
 */

namespace App\Components;

use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class LocaleSwitcherControl extends BaseControl
{
    /** @var array */
    private $localization; // check config.neon

    /** @var ITranslator */
    private $translator;

    /** @var string */
    private $locale;


    public function __construct(
        array $localization,
        ITranslator $translator
    ) {
        $this->localization = $localization;
        $this->translator = $translator;
    }


    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/localeSwitcher.latte');

        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator->domain('admin.localization'));

        $form->addSelect('locale', null)
                ->setItems($this->localization['locales'])
                ->setDefaultValue($this->localization['defaultLocale'])
                ->setTranslator(null);

        if (isset($this->locale) and array_key_exists($this->locale, $this->localization['locales'])) {
            $form['locale']->setValue($this->locale);
        }

        $form->addSubmit('change', 'form.change.caption');

        $form->onSuccess[] = [$this, 'processLocale'];

        return $form;
    }


    public function processLocale(Form $form, $values)
    {
        $this->presenter->redirect('this', ['locale' => $values->locale]);
    }
}


interface ILocaleSwitcherControlFactory
{
    /**
     * @return LocaleSwitcherControl
     */
    public function create();
}