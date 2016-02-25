<?php

namespace Options\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Doctrine\DBAL\DBALException;
use Nette\Localization\ITranslator;
use Options\Facades\OptionFacade;
use Nette\Application\UI\Form;
use Nette\Utils\Validators;

class OptionsPresenter extends ProtectedPresenter
{
    /**
     * @var OptionFacade
     * @inject
     */
    public $optionFacade;


    public function actionDefault()
    {
        $this['pageTitle']->setPageTitle('options.title');

        $this['optionsForm']->setDefaults($this->options);
    }


    public function renderDefault()
    {
        
    }


    /**
     * @Actions default
     */
    protected function createComponentOptionsForm()
    {
        $form = new Form;
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

        $form->addSubmit('save', 'save.caption');

        $form->onSuccess[] = [$this, 'processForm'];

        return $form;
    }


    public function processForm(Form $form, $values)
    {
        $options = $this->prepareOptions($this->optionFacade->findOptions());
        foreach ((array)$values as $key => $value) {
            $options[$key]->value = $value;
        }

        try {
            $this->optionFacade->saveOptions($options);

            $this->flashMessage('options.form.messages.success', 'success');
            $this->redirect('this');

        } catch (DBALException $e) {
            $form->addError($this->translator->translate('options.form.messages.savingError')); // todo
        }
    }


    private function prepareOptions(array $options)
    {
        $result = [];
        foreach ($options as $option) {
            $result[$option->name] = $option;
        }

        return $result;
    }
}