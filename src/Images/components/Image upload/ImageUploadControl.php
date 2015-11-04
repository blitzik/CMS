<?php

namespace Images\Components;

use App\Exceptions\Runtime\FileSizeException;
use App\Exceptions\Runtime\NotImageUploadedException;
use Doctrine\DBAL\DBALException;
use Images\Facades\ImageFacade;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use App\BaseControl;
use Images\Image;
use Nette\InvalidStateException;

class ImageUploadControl extends BaseControl
{
    /** @var ImageFacade  */
    private $imageFacade;

    public function __construct(
        ImageFacade $imageFacade
    ) {
        $this->imageFacade = $imageFacade;
    }

    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/imageUpload.latte');

        $template->render();
    }

    protected function createComponentImageUpload()
    {
        $form = new Form();

        $form->addUpload('image', 'Vyberte obrázek (max. 1MB)')
            ->addRule(Form::IMAGE, 'Lze nahrávat pouze obrázky. (jpg, gif, png)')
            ->addRule(Form::MAX_FILE_SIZE, 'Lze nahrát obrázek s max. velikostí do 1MB', Image::MAX_FILE_SIZE);

        $form->addSubmit('upload', 'Nahrát obrázek');

        $form->onSuccess[] = [$this, 'processImageUpload'];

        return $form;
    }

    public function processImageUpload(Form $form, $values)
    {
        /** @var FileUpload $image */
        $image = $values->image;

        try {
            if ($image->isOk()) {
                $this->imageFacade->saveImage($image);
                $this->flashMessage('Obrázek byl úspěšně nahrán', 'success');
                $this->redirect('this');
            }
        } catch (NotImageUploadedException $iu) {
            $form->addError('Lze nahrávat pouze obrázky');

        } catch (FileSizeException $fs) {
            $form->addError('Lze nahrávat obrázky o max. velikosti ' . (Image::MAX_FILE_SIZE / 1048576) . 'MB');

        } catch (InvalidStateException $is) {
            $form->addError('Při nahrávání obrázku došlo k chybě');

        } catch (DBALException $e) {
            $form->addError('Při nahrávání obrázku došlo k chybě');
        }

    }
}


interface IImageUploadControlFactory
{
    /**
     * @return ImageUploadControl
     */
    public function create();
}