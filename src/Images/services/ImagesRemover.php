<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 17.02.2016
 */

namespace Images\Services;

use Images\Exceptions\Runtime\FileRemovalException;
use Doctrine\DBAL\DBALException;
use Images\Image;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Utils\FileSystem;

class ImagesRemover extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var string */
    private $imageFileRoot;


    public function __construct(
        $imageFileRoot,
        EntityManager $entityManager
    ) {
        $this->imageFileRoot = $imageFileRoot;
        $this->em = $entityManager;
    }


    /**
     * @param string $imageName image name is comprised of UUID and original name (UUID/origName.extension)
     * @throws DBALException
     * @throws FileRemovalException
     */
    public function removeImage($imageName)
    {
        $id = mb_substr($imageName, 0, mb_strpos($imageName, '/'));
        $file = sprintf('%s/%s', $this->imageFileRoot, $imageName);

        try {
            $this->em->beginTransaction();

            $d = $this->em->createQuery(
                'DELETE ' . Image::class . ' i WHERE i.id = :id'
            )->execute(['id' => $id]);

            $directory = sprintf('%s/%s', $this->imageFileRoot, $id);
            // checks whether directory exists (each directory has always one file)
            if ($d > 0 and \file_exists($directory) and is_dir($directory)) {
                $r = \unlink($file); // and if so then remove file in it
                if ($r === false) { // file couldn't be removed
                    $this->em->rollback();
                    $this->em->close();
                    throw new FileRemovalException;
                } else {
                    // remove directory
                    FileSystem::delete($directory);
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