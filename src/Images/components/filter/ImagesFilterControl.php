<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 17.02.2016
 */

namespace Images\Components;

use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

class ImagesFilterControl extends BaseControl
{

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/imagesFilter.latte');



        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form;

        $form->addText('name', 'Název:', 30);

        $form->addSelect('extension', 'Přípona:', ['png' => 'PNG', 'jpg' => 'JPG'])
                ->setPrompt('Vše');

        $form->addText('maxWidth', 'Max. šířka:')
                ->addCondition(Form::FILLED)
                ->addRule(Form::INTEGER, 'Do pole maximální šířky lze zadávat pouze celá čísla')
                ->addRule(Form::MIN, 'Do pole maximální šířky lze zadávat pouze celá čísla větší než 0', 1);

        $form->addText('maxHeight', 'Max. výška:')
                ->addCondition(Form::FILLED)
                ->addRule(Form::INTEGER, 'Do pole maximální výšky lze zadávat pouze celá čísla')
                ->addRule(Form::MIN, 'Do pole maximální výšky lze zadávat pouze celá čísla větší než 0', 1);;

        $form->addSubmit('filter', 'Vyhledat')
                ->onClick[] = [$this, 'processFilter'];

        $form->addSubmit('reset', 'Reset filtru')
                ->onClick[] = [$this, 'resetFilter'];


        return $form;
    }


    public function processFilter(SubmitButton $buttonControl)
    {
        $values = $buttonControl->getForm()->getValues();

        foreach ($values as $k => $v) {
            if ($v === '') {
                $values[$k] = null;
            }
        }

        $this->presenter
             ->redirect(
                 'this',
                 [
                     'name' => $values['name'],
                     'extension' => $values['extension'],
                     'maxWidth' => $values['maxWidth'],
                     'maxHeight' => $values['maxHeight']
                 ]
             );
    }


    public function resetFilter(SubmitButton $buttonControl)
    {
        $this->presenter
            ->redirect(
                'this',
                [
                    'name' => null,
                    'extension' => null,
                    'maxWidth' => null,
                    'maxHeight' => null
                ]
            );
    }
}


interface IImagesFilterControlFactory
{
    /**
     * @return ImagesFilterControl
     */
    public function create();
}