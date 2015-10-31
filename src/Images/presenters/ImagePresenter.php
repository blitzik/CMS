<?php

namespace Images\Presenters;

use App\AdminModule\Presenters\ProtectedPresenter;
use Images\Facades\ImageFacade;
use Nette\Application\UI\Form;

class ImagePresenter extends ProtectedPresenter
{
    /**
     * @var ImageFacade
     * @inject
     */
    public $imageFacade;

    public function actionDefault()
    {

    }

    public function renderDefault()
    {

    }

    protected function createComponentImageUpload()
    {
        $form = new Form;

        $form->addUpload('image', 'Vyberte obrázek')
                ->setRequired('Vyberte obrázek')
                ->addRule(Form::IMAGE, 'Lze nahrávat pouze obrázky. (jpg, gif, png)')
                ->addRule(Form::MAX_FILE_SIZE, 'Lze nahrát obrázek s max. velikostí do 1MB', 1 * 1024 * 1024); // 1MB

        $form->addSubmit('upload', 'Nahrát obrázek');

        $form->onSuccess[] = [$this, 'processImageUpload'];

        return $form;
    }

    public function processImageUpload(Form $form, $values)
    {
        // todo
    }
}