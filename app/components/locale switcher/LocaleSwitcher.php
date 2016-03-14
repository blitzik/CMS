<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 23.02.2016
 */

namespace App\Components;

use Localization\Facades\LocaleFacade;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Localization\ITranslator;

class LocaleSwitcherControl extends BaseControl
{
    /** @var ITranslator */
    private $translator;

    /** @var SessionSection */
    private $session;

    /** @var string */
    private $locale;

    /** @var array */
    private $locales;


    public function __construct(
        LocaleFacade $localeFacade,
        ITranslator $translator,
        Session $session
    ) {
        $this->translator = $translator;
        $this->session = $session->getSection('cms_localization');

        $this->prepareLocales($localeFacade->findAllLocales());
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
                ->setItems($this->locales)
                ->setDefaultValue($this->locale)
                ->setTranslator(null);

        if (isset($this->locale) and array_key_exists($this->locale, $this->locales)) {
            $form['locale']->setValue($this->locale);
        }

        $form->addSubmit('change', 'form.change.caption');

        $form->onSuccess[] = [$this, 'processLocale'];

        return $form;
    }


    public function processLocale(Form $form, $values)
    {
        $this->session->locale = $values->locale;
        $this->presenter->redirect('this', ['locale' => $values->locale]);
    }


    private function prepareLocales(array $locales)
    {
        foreach ($locales as $locale) {
            $this->locales[$locale['code']] = $locale['code'];
        }
    }
}


interface ILocaleSwitcherControlFactory
{
    /**
     * @return LocaleSwitcherControl
     */
    public function create();
}