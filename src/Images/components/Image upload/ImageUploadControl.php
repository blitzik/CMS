<?php

namespace Images\Components;

use App\Components\BaseControl;
use blitzik\FlashMessages\FlashMessage;
use Images\Exceptions\Runtime\FileSizeException;
use Images\Exceptions\Runtime\NotImageUploadedException;
use Doctrine\DBAL\DBALException;
use Images\Facades\ImageFacade;
use Kdyby\Translation\Phrase;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Images\Image;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;

class ImageUploadControl extends BaseControl
{
    /** @var ImageFacade  */
    private $imageFacade;

    /** @var ITranslator */
    private $translator;

    /** @var string */
    private $imageSize;

    public function __construct(
        ImageFacade $imageFacade,
        ITranslator $translator
    ) {
        $this->imageFacade = $imageFacade;
        $this->translator = $translator;
        $this->imageSize = (Image::MAX_FILE_SIZE / 1048576) . 'MB';
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
        $form->setTranslator($this->translator->domain('images.uploadForm'));

        $form->addUpload('image', new Phrase('image.label', ['size' => $this->imageSize]))
                ->addRule(Form::IMAGE, 'image.messages.imageFile')
                ->addRule(Form::MAX_FILE_SIZE, new Phrase('image.messages.maxFileSize', ['size' => $this->imageSize]), Image::MAX_FILE_SIZE);

        $form->addSubmit('upload', 'upload.caption');

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
                $this->flashMessage('images.uploadForm.messages.success', FlashMessage::SUCCESS);
                $this->redirect('this');
            }
        } catch (NotImageUploadedException $iu) {
            $form->addError($this->translator->translate('images.uploadForm.messages.wrongFileType'));

        } catch (FileSizeException $fs) {
            $form->addError($this->translator->translate('images.uploadForm.messages.wrongFileSize', ['size' => $this->imageSize]));

        } catch (InvalidStateException $is) {
            $form->addError($this->translator->translate('images.uploadForm.messages.savingError'));

        } catch (DBALException $e) {
            $form->addError($this->translator->translate('images.uploadForm.messages.savingError'));
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