<?php

namespace Images\Facades;

use Images\Exceptions\Runtime\FileRemovalException;
use Images\Exceptions\Runtime\NotImageUploadedException;
use Images\Exceptions\Runtime\FileSizeException;
use Images\Query\ImageQuery;
use Images\Services\ImagesRemover;
use Images\Services\ImagesUploader;
use Nette\InvalidStateException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\FileUpload;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Images\Image;

class ImageFacade extends Object
{
    /** @var ImagesUploader */
    private $imagesUploader;

    /** @var EntityManager */
    private $em;

    /** @var ImagesRemover */
    private $imagesRemover;

    /** @var Logger  */
    private $logger;

    /** @var \Kdyby\Doctrine\EntityRepository  */
    private $imageRepository;


    public function __construct(
        ImagesUploader $imagesUploader,
        ImagesRemover $imagesRemover,
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->imagesUploader = $imagesUploader;
        $this->imagesRemover = $imagesRemover;
        $this->em = $entityManager;
        $this->logger = $logger->channel('images');

        $this->imageRepository = $this->em->getRepository(Image::class);
    }


    /**
     * @param FileUpload $file
     * @throws NotImageUploadedException
     * @throws FileSizeException
     * @throws DBALException
     * @throws InvalidStateException
     */
    public function saveImage(FileUpload $file)
    {
        $this->imagesUploader->processImage($file);
    }


    public function fetchImages(ImageQuery $imageQuery)
    {
        return $this->imageRepository->fetch($imageQuery);
    }


    /**
     * @param string $imageName [uuid/origName.extension]
     * @throws DBALException
     * @throws FileRemovalException
     */
    public function removeImage($imageName)
    {
        $this->imagesRemover->removeImage($imageName);
    }
}