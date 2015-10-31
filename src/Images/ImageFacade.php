<?php

namespace Images\Facades;

use App\Exceptions\Runtime\FileUploadException;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\FileUpload;
use Nette\Object;

class ImageFacade extends Object
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    public function saveImage(FileUpload $file)
    {
        if (!$file->isOk()) {
            throw new FileUploadException;
        }

        // todo
    }
}