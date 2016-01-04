<?php

namespace Images\Facades;

use App\Exceptions\Runtime\FileRemovalException;
use App\Exceptions\Runtime\NotImageUploadedException;
use App\Exceptions\Runtime\FileSizeException;
use Images\Query\ImageQuery;
use Nette\InvalidStateException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\FileUpload;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Images\Image;

class ImageFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Logger  */
    private $logger;

    /** @var \Kdyby\Doctrine\EntityRepository  */
    private $imageRepository;


    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
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

            $file->move($image->getLocation()); // todo images

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
    }


    public function fetchImages(ImageQuery $imageQuery)
    {
        return $this->imageRepository->fetch($imageQuery);
    }


    /**
     * @param string $imageName [uuid.extension]
     * @throws DBALException
     * @throws FileRemovalException
     */
    public function removeImage($imageName)
    {
        $id = \mb_substr($imageName, 0, \mb_strrpos($imageName, '.'));
        $file = Image::UPLOAD_DIRECTORY . $imageName;

        try {
            $this->em->beginTransaction();

            $d = $this->em->createQuery(
                'DELETE ' . Image::class . ' i WHERE i.id = :id'
            )->execute(['id' => $id]);

            if ($d > 0 and \file_exists($file)) {
                $r = \unlink($file);
                if ($r === false) {
                    $this->em->rollback();
                    $this->em->close();
                    throw new FileRemovalException;
                }
            }

            $this->em->commit();

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            throw $e;
        }
    }
}