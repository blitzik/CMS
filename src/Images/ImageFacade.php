<?php

namespace Images\Facades;

use App\Exceptions\Runtime\FileSizeException;
use App\Exceptions\Runtime\NotImageUploadedException;
use Doctrine\DBAL\DBALException;
use Images\Image;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Http\FileUpload;
use Nette\Object;

class ImageFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Logger  */
    private $logger;

    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('images');
    }


    public function saveImage(FileUpload $file)
    {
        if (!$file->isImage()) {
            throw new NotImageUploadedException;
        }

        if (\filesize($file->getTemporaryFile()) > Image::MAX_FILE_SIZE) {
            throw new FileSizeException;
        }

        try {
            $image = new Image($file);
            $this->em->persist($image)->flush();

            $file->move($image->getLocation());


        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('Image error'); // todo err message

            throw $e;
        }
    }
}