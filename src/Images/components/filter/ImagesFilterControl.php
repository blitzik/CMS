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
use Nette\Localization\ITranslator;

class ImagesFilterControl extends BaseControl
{
    /** @var ITranslator */
    private $translator;


    public function __construct(ITranslator $translator)
    {
        $this->translator = $translator;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/imagesFilter.latte');



        $template->render();
    }


    protected function createComponentForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator->domain('images.filterForm'));

        $form->addText('name', 'name.label', 30);

        $form->addSelect('extension', 'extension.label', ['png' => 'PNG', 'jpg' => 'JPG'])
                ->setPrompt('extension.prompt');

        $form->addText('maxWidth', 'maxWidth.label')
                ->addCondition(Form::FILLED)
                ->addRule(Form::INTEGER, 'maxWidth.messages.integerType')
                ->addRule(Form::MIN, 'maxWidth.messages.minValue', 1);

        $form->addText('maxHeight', 'maxHeight.label')
                ->addCondition(Form::FILLED)
                ->addRule(Form::INTEGER, 'maxHeight.messages.integerType')
                ->addRule(Form::MIN, 'maxHeight.messages.integerType', 1);;

        $form->addSubmit('filter', 'filter.caption')
                ->onClick[] = [$this, 'processFilter'];

        $form->addSubmit('reset', 'reset.caption')
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