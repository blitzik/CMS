<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 16.02.2016
 */

namespace Images\Services;

use Images\Exceptions\Runtime\NotImageUploadedException;
use Images\Exceptions\Runtime\FileSizeException;
use Kdyby\Doctrine\EntityManager;
use Nette\InvalidStateException;
use Doctrine\DBAL\DBALException;
use Nette\Http\FileUpload;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Images\Image;

class ImagesUploader extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $imageFileRoot;


    public function __construct(
        $imageFileRoot,
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->imageFileRoot = $imageFileRoot;
        $this->em = $entityManager;
        $this->logger = $logger->channel('images');
    }


    /**
     * @param FileUpload $file
     * @return Image
     * @throws NotImageUploadedException
     * @throws FileSizeException
     * @throws DBALException
     * @throws InvalidStateException
     */
    public function processImage(FileUpload $file)
    {
        if (!$file->isImage()) {
            throw new NotImageUploadedException;
        }

        if (\filesize($file->getTemporaryFile()) > Image::MAX_FILE_SIZE) {
            throw new FileSizeException;
        }

        try {
            $this->em->beginTransaction();
            $image = new Image($file);
            $this->em->persist($image)->flush();

            $file->move($this->composeImageLocation($image));

            $this->em->commit();

        } catch (InvalidStateException $is) {
            $this->em->rollback();
            $this->em->close();
            $this->logger->addError('Error occurs while moving temp. image file to new location.');

            throw $is;

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('Image error'); // todo err message

            throw $e;
        }

        return $image;
    }


    /**
     * @param Image $image
     * @return string
     */
    private function composeImageLocation(Image $image)
    {
        return sprintf(
            '%s/%s',
            $this->imageFileRoot,
            $image->getComposedFilePath()
        );
    }

}