<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 05.04.2016
 */

namespace Options\Components\Admin;

use blitzik\FlashMessages\FlashMessage;
use Kdyby\Translation\Translator;
use Options\Facades\OptionFacade;
use Doctrine\DBAL\DBALException;
use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Nette\Utils\Validators;

class OptionsFormControl extends BaseControl
{
    /** @var OptionFacade */
    private $optionFacade;

    /** @var Translator  */
    private $translator;


    public function __construct(
        OptionFacade $optionFacade,
        Translator $translator
    ) {
        $this->optionFacade = $optionFacade;
        $this->translator = $translator;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/options.latte');


        $template->render();
    }


    protected function createComponentOptionsForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator->domain('options.form'));

        $form->addText('blog_title', 'blogTitle.label', null, 255)
                ->setRequired('blogTitle.messages.required');

        $form->addText('blog_subtitle', 'blogSubtitle.label', null, 255);

        $form->addText('copyright', 'copyright.label', null, 255)
                ->setRequired('copyright.messages.required');

        $form->addText('articles_per_page', 'articlesPerPage.label', null, 2)
                ->setRequired('articlesPerPage.messages.required')
                ->addRule(function ($input) {
                    if (Validators::is($input->value, 'numericint:1..')) {
                        return true;
                    }
                    return false;
                }, 'articlesPerPage.messages.wrongInput');

        $form->addText('google_analytics_measure_code', 'gaMeasureCode.label');

        $form->addSubmit('save', 'save.caption');

        $form->onSuccess[] = [$this, 'processForm'];

        $form->addProtection();

        if (!$this->authorizator->isAllowed($this->user, 'options', 'edit')) {
            $form['save']->setDisabled();
        }

        $form->setDefaults($this->optionFacade->loadOptions());

        return $form;
    }


    public function processForm(Form $form, $values)
    {
        if (!$this->authorizator->isAllowed($this->user, 'options', 'edit')) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            $this->redirect('this');
        }
        try {
            $this->optionFacade->saveOptions((array)$values);
            $this->flashMessage('options.form.messages.success', FlashMessage::SUCCESS);
            $this->redirect('this');

        } catch (DBALException $e) {
            $form->addError($this->translator->translate('options.form.messages.savingError'));
        }
    }
}


interface IOptionsFormControlFactory
{
    /**
     * @return OptionsFormControl
     */
    public function create();
}