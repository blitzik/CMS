<?php

namespace Options\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Doctrine\DBAL\DBALException;
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
        $this['pageTitle']->setPageTitle('Nastavení blogu');

        $this['optionsForm']->setDefaults($this->options);
    }


    public function renderDefault()
    {
        
    }


    protected function createComponentOptionsForm()
    {
        $form = new Form;

        $form->addText('blog_title', 'Název blogu (*)', null, 255)
                ->setRequired('Název blogu je povinná položka');

        $form->addText('blog_subtitle', 'Popisek blogu', null, 255);

        $form->addText('copyright', 'Copyright (*)', null, 255)
                ->setRequired('Vyplňte, komu náleží práva toho Blogu');

        $form->addText('articles_per_page', 'Počet článků na stránku (*)', null, 2)
                ->setRequired('Nastavte počet článků zobrazujících se na jedné stránce.')
                ->addRule(function ($input) {
                    if (Validators::is($input->value, 'numericint:1..')) {
                        return true;
                    }
                    return false;
                }, 'Do pole "počet článků na stránku" lze zadat pouze přirozená čísla.');

        $form->addSubmit('save', 'Uložit nastavení');

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

            $this->flashMessage('Změny byly úspěšně uloženy', 'success');
            $this->redirect('this');

        } catch (DBALException $e) {
            $form->addError('');
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